<?php

namespace App\Filament\Resources\MarcendiserResource\RelationManagers;

use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Placeholder::make('po.no_po')
                        ->label('No. PO')
                        ->inlineLabel()
                        ->content(fn(Invoice $record): string => $record->po->no_po),
                    Forms\Components\TextInput::make('no_invoice')
                        ->required()
                        ->maxLength(255)
                        ->inlineLabel(),

                ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->poll('3s')
            ->heading('Invoice')
            ->recordTitleAttribute('no_invoice')
            ->columns([
                Tables\Columns\TextColumn::make('po.vendor.name')
                    ->label('Supplier'),
                Tables\Columns\TextColumn::make('po.no_po')
                    ->label('No. PO'),
                Tables\Columns\TextColumn::make('no_invoice'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
        ;
    }
}
