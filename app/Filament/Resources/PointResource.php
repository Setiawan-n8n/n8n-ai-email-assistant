<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointResource\Pages;
use App\Models\Wesel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PointResource extends Resource
{
    protected static ?string $model = Wesel::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Persinyalan';

    protected static ?string $navigationLabel = 'Wesel';

    protected static ?string $modelLabel = 'Wesel';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Kode Wesel')->required()->maxLength(20),
            Forms\Components\Select::make('track_from_id')
                ->label('Dari Jalur')
                ->relationship('trackFrom', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('track_to_id')
                ->label('Ke Jalur')
                ->relationship('trackTo', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('side')
                ->label('Sisi')
                ->options(['barat' => 'Barat (Wonokromo)', 'timur' => 'Timur (Sidotopo)'])
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
                Tables\Columns\TextColumn::make('trackFrom.name')->label('Dari Jalur'),
                Tables\Columns\TextColumn::make('trackTo.name')->label('Ke Jalur'),
                Tables\Columns\TextColumn::make('side')->label('Sisi')->badge()->color(fn (string $state) => $state === 'barat' ? 'primary' : 'success')->formatStateUsing(fn ($state) => $state === 'barat' ? 'Barat' : 'Timur'),
            ])
            ->filters([
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
            'index' => Pages\ListPoints::route('/'),
            'create' => Pages\CreatePoint::route('/create'),
            'edit' => Pages\EditPoint::route('/{record}/edit'),
        ];
    }
}
