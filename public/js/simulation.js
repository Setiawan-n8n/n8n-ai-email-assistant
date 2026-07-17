(function () {
    'use strict';

    var NS = 'http://www.w3.org/2000/svg';
    var CFG = window.SIMULATION_CONFIG || {};

    // ---- Layout constants (harus sinkron dengan posisi X/Y default di database) ----
    var TRACK_Y = [90, 160, 230, 300, 370, 440]; // urut sesuai sort_order jalur
    var TRUNK_Y = (TRACK_Y[0] + TRACK_Y[TRACK_Y.length - 1]) / 2;
    var X_EDGE_BARAT = 10;
    var X_TRUNK_BARAT = 170;
    var X_WESEL_BARAT = 280;
    var X_STATION_LEFT = 280;
    var X_STATION_RIGHT = 920;
    var X_WESEL_TIMUR = 920;
    var X_TRUNK_TIMUR = 1030;
    var X_EDGE_TIMUR = 1190;
    var X_HOME = (X_STATION_LEFT + X_STATION_RIGHT) / 2;

    var APPROACH_MIN = 4;   // menit animasi masuk/keluar
    var DWELL_STATIC = 3;   // menit KA "muncul" statis sebelum berangkat (tanpa jam datang)
    var ARRIVAL_ONLY_DWELL = 45; // menit KA tetap tampil "berhenti" setelah datang jika tidak ada jam berangkat tercatat (mis. rangkaian lalu dipindah ke dipo)

    var KATEGORI_COLOR = {
        penumpang: '#3ca7f6',
        komuter: '#2dd4bf',
        barang: '#f97316',
        dinas: '#94a3b8',
        langsir: '#94a3b8',
        lainnya: '#c084fc',
    };

    var state = {
        data: null,
        trackYByCode: {},
        trackIdToCode: {},
        clockMin: 300,
        playing: false,
        speed: 30,
        timer: null,
    };

    // ---------------------------------------------------------------------
    // Utilities
    // ---------------------------------------------------------------------
    function el(tag, attrs, parent) {
        var e = document.createElementNS(NS, tag);
        for (var k in attrs) {
            if (Object.prototype.hasOwnProperty.call(attrs, k)) {
                e.setAttribute(k, attrs[k]);
            }
        }
        if (parent) parent.appendChild(e);
        return e;
    }

    function timeToMin(t) {
        if (!t) return null;
        var parts = t.split(':');
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    }

    function fmtClock(min) {
        min = ((Math.floor(min) % 1440) + 1440) % 1440;
        var h = Math.floor(min / 60);
        var m = min % 60;
        return (h < 10 ? '0' + h : h) + ':' + (m < 10 ? '0' + m : m);
    }

    function lerp(a, b, t) {
        t = Math.max(0, Math.min(1, t));
        return a + (b - a) * t;
    }

    function sideEdgeX(side) {
        return side === 'timur' ? X_EDGE_TIMUR : X_EDGE_BARAT;
    }

    // ---------------------------------------------------------------------
    // Data loading
    // ---------------------------------------------------------------------
    function loadData(tanggal) {
        var url = new URL(CFG.apiUrl, window.location.origin);
        if (tanggal) url.searchParams.set('tanggal', tanggal);

        return fetch(url.toString())
            .then(function (res) { return res.json(); })
            .then(function (data) {
                state.data = data;
                state.trackYByCode = {};
                state.trackIdToCode = {};
                data.tracks.forEach(function (t, i) {
                    var y = TRACK_Y[i] !== undefined ? TRACK_Y[i] : (90 + i * 70);
                    state.trackYByCode[t.code] = y;
                    state.trackIdToCode[t.id] = t.code;
                });
                return data;
            });
    }

    // ---------------------------------------------------------------------
    // Train phase / position calculation
    // ---------------------------------------------------------------------
    function getPhase(row, t) {
        var trackCode = row.track ? row.track.code : null;
        var y = trackCode !== undefined ? state.trackYByCode[trackCode] : undefined;
        if (y === undefined) {
            return { visible: false };
        }

        var dat = timeToMin(row.jam_datang);
        var ber = timeToMin(row.jam_berangkat);
        var asalSide = row.asal ? row.asal.side : 'barat';
        var tujuanSide = row.tujuan ? row.tujuan.side : 'barat';
        var isThrough = !dat && row.jam_datang_ket === 'Ls';

        // Kasus: lewat langsung tanpa berhenti (hanya BER, DAT = "Ls")
        if (isThrough && ber !== null) {
            var startX = sideEdgeX(asalSide);
            var endX = sideEdgeX(tujuanSide);
            var winStart = ber - APPROACH_MIN;
            var winEnd = ber + APPROACH_MIN;
            if (t < winStart || t > winEnd) return { visible: false };
            var progress = (t - winStart) / (winEnd - winStart);
            return {
                visible: true,
                x: lerp(startX, endX, progress),
                y: y,
                phase: 'through',
                sideActive: t < ber ? asalSide : tujuanSide,
                trackCode: trackCode,
            };
        }

        // Kasus: datang & berangkat sama-sama ada (transit / berhenti)
        if (dat !== null && ber !== null) {
            var enterStart = dat - APPROACH_MIN;
            var leaveEnd = ber + APPROACH_MIN;
            if (t < enterStart || t > leaveEnd) return { visible: false };

            if (t < dat) {
                var p1 = (t - enterStart) / APPROACH_MIN;
                return { visible: true, x: lerp(sideEdgeX(asalSide), X_HOME, p1), y: y, phase: 'masuk', sideActive: asalSide, trackCode: trackCode };
            }
            if (t <= ber) {
                return { visible: true, x: X_HOME, y: y, phase: 'berhenti', trackCode: trackCode };
            }
            var p2 = (t - ber) / APPROACH_MIN;
            return { visible: true, x: lerp(X_HOME, sideEdgeX(tujuanSide), p2), y: y, phase: 'keluar', sideActive: tujuanSide, trackCode: trackCode };
        }

        // Kasus: hanya jam datang (tidak ada jam berangkat tercatat di baris
        // ini -- biasanya karena rangkaiannya dipindah/dilangsir dan dicatat
        // sebagai baris "Dinas Rangkaian" terpisah). KA ditampilkan masuk &
        // berhenti selama ARRIVAL_ONLY_DWELL menit, lalu dianggap sudah
        // dipindah ke dipo sehingga tidak menumpuk terus di daftar.
        if (dat !== null) {
            var enterStart2 = dat - APPROACH_MIN;
            var dwellEnd2 = dat + ARRIVAL_ONLY_DWELL;
            if (t < enterStart2 || t > dwellEnd2) return { visible: false };
            if (t < dat) {
                var p3 = (t - enterStart2) / APPROACH_MIN;
                return { visible: true, x: lerp(sideEdgeX(asalSide), X_HOME, p3), y: y, phase: 'masuk', sideActive: asalSide, trackCode: trackCode };
            }
            return { visible: true, x: X_HOME, y: y, phase: 'berhenti', trackCode: trackCode };
        }

        // Kasus: hanya jam berangkat (mis. dinas rangkaian berangkat dari jalur ini)
        if (ber !== null) {
            var dwellStart = ber - DWELL_STATIC;
            var leaveEnd2 = ber + APPROACH_MIN;
            if (t < dwellStart || t > leaveEnd2) return { visible: false };
            if (t <= ber) {
                return { visible: true, x: X_HOME, y: y, phase: 'berhenti', trackCode: trackCode };
            }
            var p4 = (t - ber) / APPROACH_MIN;
            return { visible: true, x: lerp(X_HOME, sideEdgeX(tujuanSide), p4), y: y, phase: 'keluar', sideActive: tujuanSide, trackCode: trackCode };
        }

        return { visible: false };
    }

    function computeSignalStates(t) {
        // key: "<trackCode>|<side>" -> boolean merah
        var red = {};
        if (!state.data) return red;

        state.data.jadwal.forEach(function (row) {
            var ph = getPhase(row, t);
            if (!ph.visible) return;
            if (ph.phase === 'masuk' || ph.phase === 'keluar' || ph.phase === 'through') {
                var key = ph.trackCode + '|' + ph.sideActive;
                red[key] = true;
                if (ph.phase === 'through') {
                    // tandai kedua sisi selama lintas langsung
                    var otherSide = ph.sideActive === 'barat' ? 'timur' : 'barat';
                    red[ph.trackCode + '|' + otherSide] = true;
                }
            }
        });

        return red;
    }

    // ---------------------------------------------------------------------
    // Rendering
    // ---------------------------------------------------------------------
    function renderStatic() {
        var svg = document.getElementById('stationSvg');
        svg.innerHTML = '';

        var defs = el('defs', {}, svg);
        var glow = el('filter', { id: 'glow', x: '-60%', y: '-60%', width: '220%', height: '220%' }, defs);
        el('feGaussianBlur', { stdDeviation: '2.2', result: 'blur' }, glow);
        var merge = el('feMerge', {}, glow);
        el('feMergeNode', { in: 'blur' }, merge);
        el('feMergeNode', { in: 'SourceGraphic' }, merge);

        var g = el('g', { id: 'layer-track' }, svg);
        el('g', { id: 'layer-station' }, svg);
        el('g', { id: 'layer-signal' }, svg);
        el('g', { id: 'layer-wesel' }, svg);
        el('g', { id: 'layer-train' }, svg);

        var tracks = (state.data && state.data.tracks) || [];

        tracks.forEach(function (tr, i) {
            var y = TRACK_Y[i] !== undefined ? TRACK_Y[i] : (90 + i * 70);

            // trunk barat
            el('line', { x1: X_EDGE_BARAT, y1: TRUNK_Y, x2: X_TRUNK_BARAT, y2: TRUNK_Y, class: 'track-line' }, g);
            // fan barat
            el('path', {
                d: 'M ' + X_TRUNK_BARAT + ' ' + TRUNK_Y + ' C ' + (X_TRUNK_BARAT + 60) + ' ' + TRUNK_Y + ', ' + (X_WESEL_BARAT - 40) + ' ' + y + ', ' + X_WESEL_BARAT + ' ' + y,
                class: 'track-line',
            }, g);
            // badan jalur (area stasiun)
            el('line', { x1: X_WESEL_BARAT, y1: y, x2: X_WESEL_TIMUR, y2: y, class: 'track-line' }, g);
            // fan timur
            el('path', {
                d: 'M ' + X_WESEL_TIMUR + ' ' + y + ' C ' + (X_WESEL_TIMUR + 40) + ' ' + y + ', ' + (X_TRUNK_TIMUR - 60) + ' ' + TRUNK_Y + ', ' + X_TRUNK_TIMUR + ' ' + TRUNK_Y,
                class: 'track-line',
            }, g);
            // trunk timur
            el('line', { x1: X_TRUNK_TIMUR, y1: TRUNK_Y, x2: X_EDGE_TIMUR, y2: TRUNK_Y, class: 'track-line' }, g);

            el('text', { x: X_WESEL_BARAT - 26, y: y + 4, class: 'track-label', 'text-anchor': 'end' }, g).textContent = tr.code;
            el('text', { x: X_WESEL_TIMUR + 26, y: y + 4, class: 'track-label' }, g).textContent = tr.code;
        });

        var stationLayer = document.getElementById('layer-station');
        var topY = (TRACK_Y[0] || 90) - 35;
        var botY = (TRACK_Y[TRACK_Y.length - 1] || 440) + 35;
        el('rect', {
            x: X_STATION_LEFT, y: topY, width: (X_STATION_RIGHT - X_STATION_LEFT), height: (botY - topY),
            class: 'station-box', rx: 10, fill: '#17233a', opacity: 0.35,
        }, stationLayer);
        el('text', { x: (X_STATION_LEFT + X_STATION_RIGHT) / 2, y: topY - 12, class: 'station-box-label', 'text-anchor': 'middle' }, stationLayer)
            .textContent = 'STASIUN SURABAYA GUBENG (SGU)';

        el('text', { x: X_EDGE_BARAT, y: botY + 28, class: 'side-label' }, stationLayer).textContent = '← Arah Wonokromo';
        el('text', { x: X_EDGE_TIMUR, y: botY + 28, class: 'side-label', 'text-anchor': 'end' }, stationLayer).textContent = 'Arah Sidotopo / Surabaya Kota →';

        // Wesel markers
        var weselLayer = document.getElementById('layer-wesel');
        (state.data.wesels || []).forEach(function (w) {
            if (w.pos_x == null || w.pos_y == null) return;
            el('rect', { x: w.pos_x - 4, y: w.pos_y - 4, width: 8, height: 8, class: 'wesel-mark', transform: 'rotate(45 ' + w.pos_x + ' ' + w.pos_y + ')' }, weselLayer);
        });
    }

    function renderDynamic() {
        var t = state.clockMin;
        var redSignals = computeSignalStates(t);

        // Sinyal
        var signalLayer = document.getElementById('layer-signal');
        signalLayer.innerHTML = '';
        (state.data.signals || []).forEach(function (s) {
            if (s.pos_x == null || s.pos_y == null) return;
            var trackCode = state.trackIdToCode[s.track_id];
            var key = trackCode + '|' + s.side;
            var isRed = !!redSignals[key];
            var grp = el('g', { class: 'signal-node' }, signalLayer);
            el('line', { x1: s.pos_x, y1: s.pos_y - 14, x2: s.pos_x, y2: s.pos_y + 6, stroke: '#4b5b7d', 'stroke-width': 2 }, grp);
            el('circle', {
                cx: s.pos_x, cy: s.pos_y - 16, r: 6,
                class: 'signal-dot',
                fill: isRed ? '#ef4444' : '#33d17a',
                filter: 'url(#glow)',
            }, grp);
        });

        // Kereta
        var trainLayer = document.getElementById('layer-train');
        trainLayer.innerHTML = '';
        var atStation = [];
        var upcoming = [];

        state.data.jadwal.forEach(function (row) {
            var ph = getPhase(row, t);
            if (ph.visible) {
                drawTrain(trainLayer, row, ph);
                if (ph.phase === 'berhenti') atStation.push(row);
            }
            var dat = timeToMin(row.jam_datang);
            var ber = timeToMin(row.jam_berangkat);
            var nextT = dat !== null ? dat : ber;
            if (nextT !== null && nextT >= t && nextT <= t + 30) {
                upcoming.push(row);
            }
        });

        renderSidePanels(atStation, upcoming);
        document.getElementById('clockReadout').textContent = fmtClock(t);
        document.getElementById('timeSlider').value = t;
    }

    function drawTrain(layer, row, ph) {
        var color = KATEGORI_COLOR[row.kategori] || KATEGORI_COLOR.lainnya;
        var w = 46, h = 16;
        var grp = el('g', { class: 'train-node', transform: 'translate(' + (ph.x - w / 2) + ',' + (ph.y - h / 2) + ')' }, layer);
        el('rect', { width: w, height: h, rx: 5, fill: color }, grp);
        var label = String(row.no_ka || '').slice(0, 7);
        el('text', { x: w / 2, y: h / 2 + 1 }, grp).textContent = label;
        var title = el('title', {}, grp);
        title.textContent = row.no_ka + ' - ' + row.nama_ka + ' (' + (row.relasi_raw || '-') + ')';
    }

    function trainMeta(row) {
        var jam = row.jam_datang || row.jam_berangkat || '-';
        var arah = row.jam_datang ? ('dari ' + (row.asal ? row.asal.code : '-')) : ('menuju ' + (row.tujuan ? row.tujuan.code : '-'));
        return jam + ' &middot; Jalur ' + (row.track ? row.track.code : '-') + ' &middot; ' + arah;
    }

    function renderSidePanels(atStation, upcoming) {
        var cur = document.getElementById('currentTrains');
        var up = document.getElementById('upcomingTrains');

        if (atStation.length === 0) {
            cur.innerHTML = '<p class="muted">Tidak ada KA yang sedang berhenti.</p>';
        } else {
            cur.innerHTML = atStation.map(function (row) {
                return '<div class="train-card"><span class="no-ka">' + row.no_ka + '</span><span class="nama">' + row.nama_ka + '</span>' +
                    '<div class="meta">' + trainMeta(row) + '</div></div>';
            }).join('');
        }

        upcoming.sort(function (a, b) {
            var ta = timeToMin(a.jam_datang || a.jam_berangkat);
            var tb = timeToMin(b.jam_datang || b.jam_berangkat);
            return ta - tb;
        });

        if (upcoming.length === 0) {
            up.innerHTML = '<p class="muted">Tidak ada dalam 30 menit ke depan.</p>';
        } else {
            up.innerHTML = upcoming.slice(0, 8).map(function (row) {
                return '<div class="train-card"><span class="no-ka">' + row.no_ka + '</span><span class="nama">' + row.nama_ka + '</span>' +
                    '<div class="meta">' + trainMeta(row) + '</div></div>';
            }).join('');
        }
    }

    // ---------------------------------------------------------------------
    // Playback control
    // ---------------------------------------------------------------------
    function tick() {
        state.clockMin += state.speed / 12; // dipanggil tiap 5 detik nyata -> speed menit/detik * (1/12)
        if (state.clockMin >= 1440) state.clockMin = 0;
        renderDynamic();
    }

    function play() {
        if (state.playing) return;
        state.playing = true;
        document.getElementById('btnPlay').innerHTML = '&#10074;&#10074; Jeda';
        state.timer = setInterval(tick, 200);
    }

    function pause() {
        state.playing = false;
        document.getElementById('btnPlay').innerHTML = '&#9654; Mulai';
        clearInterval(state.timer);
    }

    function bindControls() {
        document.getElementById('btnPlay').addEventListener('click', function () {
            if (state.playing) pause(); else play();
        });

        document.getElementById('btnReset').addEventListener('click', function () {
            pause();
            state.clockMin = 300;
            renderDynamic();
        });

        document.getElementById('speedSelect').addEventListener('change', function (e) {
            state.speed = parseFloat(e.target.value);
        });

        document.getElementById('timeSlider').addEventListener('input', function (e) {
            pause();
            state.clockMin = parseInt(e.target.value, 10);
            renderDynamic();
        });

        document.getElementById('tanggalSelect').addEventListener('change', function (e) {
            pause();
            var tanggal = e.target.value;
            var url = new URL(window.location.href);
            url.searchParams.set('tanggal', tanggal);
            window.location.href = url.toString();
        });
    }

    // ---------------------------------------------------------------------
    // Init
    // ---------------------------------------------------------------------
    function init() {
        bindControls();
        loadData(CFG.tanggal).then(function () {
            renderStatic();
            renderDynamic();
        }).catch(function (err) {
            console.error('Gagal memuat data jadwal:', err);
            document.getElementById('currentTrains').innerHTML =
                '<p class="muted">Gagal memuat data. Pastikan migrasi &amp; seeder sudah dijalankan.</p>';
        });
    }

    document.addEventListener('DOMContentLoaded', init);

    // expose for potential debugging/testing
    window.__simulation = { getPhase: getPhase, timeToMin: timeToMin, fmtClock: fmtClock, state: state };
})();
