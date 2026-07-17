<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StationResource\Pages;
use App\Models\Station;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StationResource extends Resource
{
    protected static ?string $model = Station::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Stasiun / Relasi';

    protected static ?string $modelLabel = 'Stasiun';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('Kode')
                ->required()
                ->maxLength(10)
                ->unique(ignoreRecord: true)
                ->helperText('Kode relasi seperti pada jadwal, mis. SGU, SB, KTG'),
            Forms\Components\TextInput::make('name')
                ->label('Nama Stasiun')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('side')
                ->label('Arah / Sisi Emplasemen')
                ->options([
                    'barat' => 'Barat (arah Wonokromo)',
                    'timur' => 'Timur (arah Sidotopo / Surabaya Kota)',
                ])
                ->required()
                ->helperText('Menentukan dari sisi mana KA relasi ini masuk/keluar pada simulasi.'),
            Forms\Components\Toggle::make('is_own_station')
                ->label('Stasiun ini sendiri (SGU)'),
            Forms\Components\Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('side')
                    ->label('Arah')
                    ->badge()
                    ->color(fn (string $state) => $state === 'barat' ? 'primary' : 'success')
                    ->formatStateUsing(fn (string $state) => $state === 'barat' ? 'Barat (Wonokromo)' : 'Timur (Sidotopo)'),
                Tables\Columns\IconColumn::make('is_own_station')->label('SGU')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('side')->options([
                    'barat' => 'Barat',
                    'timur' => 'Timur',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('code');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStations::route('/'),
            'create' => Pages\CreateStation::route('/create'),
            'edit' => Pages\EditStation::route('/{record}/edit'),
        ];
    }
}
