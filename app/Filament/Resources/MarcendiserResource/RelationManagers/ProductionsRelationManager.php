<?php

namespace App\Filament\Resources\MarcendiserResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Marcendiser;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProductionsRelationManager extends RelationManager
{
    protected static string $relationship = 'productions';

    protected static ?string $modelLabel = 'produksi';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('jumlah_item')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->numeric()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('jumlah_ea')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->numeric()
                        ->label('Jumlah EA')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('etd')
                        ->label('ETD')
                        ->columnSpanFull(),
                ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->heading('Produksi')
            ->recordTitleAttribute('jumlah_item')
            ->columns([
                Tables\Columns\TextColumn::make('jumlah_item')
                    ->numeric(decimalPlaces: 0),
                Tables\Columns\TextColumn::make('jumlah_ea')
                    ->label('Jumlah EA')
                    ->numeric(decimalPlaces: 0),
                Tables\Columns\TextColumn::make('etd')
                    ->label('ETD')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Produksi Baru')
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
