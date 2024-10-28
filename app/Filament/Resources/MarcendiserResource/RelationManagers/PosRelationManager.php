<?php

namespace App\Filament\Resources\MarcendiserResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PosRelationManager extends RelationManager
{
    protected static string $relationship = 'pos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('vendor_id')
                        ->required()
                        ->label('Supplier')
                        ->searchable()
                        ->preload()
                        ->relationship(name: 'vendor', titleAttribute: 'name'),
                    Forms\Components\TextInput::make('no_po')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('jumlah_item')
                        ->required()
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->numeric()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('jumlah_ea')
                        ->label('Jumlah EA')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->numeric()
                        ->required()
                        ->maxLength(255),

                ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->heading('Barang di pesan/PO') // Set the table heading
            ->recordTitleAttribute('no_po')
            ->columns([
                Tables\Columns\TextColumn::make('no_po')
                    ->label('No. PO'),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Supplier'),
                Tables\Columns\TextColumn::make('jumlah_item')
                    ->summarize(Sum::make())
                    ->numeric(),
                Tables\Columns\TextColumn::make('jumlah_ea')
                    ->label('Jumlah EA')
                    ->numeric()
                    ->summarize(Sum::make()),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat PO Baru')
                    ->after(function ($record) {
                        $idMarcendiser = $this->getOwnerRecord()->id;
                        $idPos = $record->id;

                        $invoice = Invoice::create([
                            'po_id' => $idPos,
                        ]);

                        DB::table('invoice_marcendiser')->insert([
                            'marcendiser_id' => $idMarcendiser,
                            'invoice_id' => $invoice->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // dd([
                        //     'id marcendiser' => $idMarcendiser,
                        //     'id PO' => $idPos
                        // ]);
                    }),
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
