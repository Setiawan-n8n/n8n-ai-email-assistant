<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrackResource\Pages;
use App\Models\Track;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrackResource extends Resource
{
    protected static ?string $model = Track::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Jalur Emplasemen';

    protected static ?string $modelLabel = 'Jalur';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Kode Jalur')->required()->maxLength(5)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
            Forms\Components\TextInput::make('jenis')->label('Jenis Sepur')->maxLength(255),
            Forms\Components\TextInput::make('sort_order')->label('Urutan Tampil')->numeric()->default(0),
            Forms\Components\Textarea::make('keterangan')->label('Keterangan')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('jenis')->label('Jenis'),
                Tables\Columns\TextColumn::make('schedules_count')->label('Jumlah Jadwal')->counts('schedules'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTracks::route('/'),
            'create' => Pages\CreateTrack::route('/create'),
            'edit' => Pages\EditTrack::route('/{record}/edit'),
        ];
    }
}
