<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SignalResource\Pages;
use App\Models\Signal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SignalResource extends Resource
{
    protected static ?string $model = Signal::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationGroup = 'Persinyalan';

    protected static ?string $navigationLabel = 'Sinyal';

    protected static ?string $modelLabel = 'Sinyal';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Kode Sinyal')->required()->maxLength(20),
            Forms\Components\Select::make('track_id')
                ->label('Jalur')
                ->relationship('track', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('side')
                ->label('Sisi')
                ->options(['barat' => 'Barat (Wonokromo)', 'timur' => 'Timur (Sidotopo)'])
                ->required(),
            Forms\Components\Select::make('jenis')
                ->label('Jenis')
                ->options([
                    'masuk' => 'Sinyal Masuk',
                    'keluar' => 'Sinyal Keluar',
                    'langsir' => 'Sinyal Langsir',
                    'blok' => 'Sinyal Blok',
                ])
                ->required(),
            Forms\Components\TextInput::make('posisi_km')->label('Posisi KM')->numeric(),
            Forms\Components\TextInput::make('pos_x')->label('Posisi X (denah SVG, 0-1200)')->numeric(),
            Forms\Components\TextInput::make('pos_y')->label('Posisi Y (denah SVG, 0-500)')->numeric(),
            Forms\Components\Textarea::make('keterangan')->label('Keterangan')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('track.name')->label('Jalur'),
                Tables\Columns\TextColumn::make('side')->label('Sisi')->badge()->color(fn (string $state) => $state === 'barat' ? 'primary' : 'success')->formatStateUsing(fn ($state) => $state === 'barat' ? 'Barat' : 'Timur'),
                Tables\Columns\TextColumn::make('jenis')->label('Jenis'),
                Tables\Columns\TextColumn::make('pos_x')->label('X'),
                Tables\Columns\TextColumn::make('pos_y')->label('Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('track_id')->relationship('track', 'name')->label('Jalur'),
                Tables\Filters\SelectFilter::make('side')->options(['barat' => 'Barat', 'timur' => 'Timur']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSignals::route('/'),
            'create' => Pages\CreateSignal::route('/create'),
            'edit' => Pages\EditSignal::route('/{record}/edit'),
        ];
    }
}
