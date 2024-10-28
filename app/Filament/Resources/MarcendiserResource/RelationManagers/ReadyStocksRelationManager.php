<?php

namespace App\Filament\Resources\MarcendiserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReadyStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'readyStocks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('jumlah_item')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('jumlah_ea')
                        ->label('Jumlah EA')
                        ->required()
                        ->maxLength(255),

                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->heading('Stok yang tersedia')
            ->recordTitleAttribute('jumlah_item')
            ->columns([
                Tables\Columns\TextColumn::make('jumlah_item')
                    ->numeric(decimalPlaces: 0),
                Tables\Columns\TextColumn::make('jumlah_ea')
                    ->numeric(decimalPlaces: 0)
                    ->label('Jumlah EA'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->createAnother(false)
                    ->visible(fn(RelationManager $livewire) => $livewire->getRelationship()->count() === 0),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
