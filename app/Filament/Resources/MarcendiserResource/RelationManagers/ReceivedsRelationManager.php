<?php

namespace App\Filament\Resources\MarcendiserResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ReceivedsRelationManager extends RelationManager
{
    protected static string $relationship = 'receiveds';

    protected static ?string $modelLabel = 'penerimaan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('jumlah_item')
                        ->numeric()
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->required(),
                    Forms\Components\TextInput::make('jumlah_ea')
                        ->required()
                        ->label('Jumlah EA')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->numeric(),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->heading('Penerimaan')
            ->recordTitleAttribute('jumlah_item')
            ->columns([
                Tables\Columns\TextColumn::make('jumlah_item')
                    ->numeric(decimalPlaces: 0),
                Tables\Columns\TextColumn::make('jumlah_ea')
                    ->label('Jumlah EA')
                    ->numeric(decimalPlaces: 0),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Penerimaan Baru')
                    ->createAnother(false)
                    ->after(function ($record) {
                        $ownerRecord = $this->getOwnerRecord();
                        $jumlah_ea = $ownerRecord->jumlah_ea;
                        $jumlah_ea_received = $record->jumlah_ea;

                        $hasil = $jumlah_ea_received / $jumlah_ea * 100;
                        $progres = floor($hasil);

                        $bobotRecord = floor(($progres / 100) * 50);

                        $ownerRecord->update([
                            'progres' => $progres,
                            'bobot' => $bobotRecord,
                        ]);

                        $ownerRecord->project->update([
                            'progres' => $this->getOwnerRecord()->bobot + $this->getOwnerRecord()->project->penagihan->bobot + $this->getOwnerRecord()->project->kontrak->bobot + $this->getOwnerRecord()->project->pengiriman->bobot,
                        ]);





                        return;
                    })
                    ->visible(fn(RelationManager $livewire) => $livewire->getRelationship()->count() === 0),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($record) {
                        $ownerRecord = $this->getOwnerRecord();
                        $jumlah_ea = $ownerRecord->jumlah_ea;
                        $jumlah_ea_received = $record->jumlah_ea;

                        $hasil = $jumlah_ea_received / $jumlah_ea * 100;
                        $progres = floor($hasil);

                        $bobotRecord = floor(($progres / 100) * 50);

                        $ownerRecord->update([
                            'progres' => $progres,
                            'bobot' => $bobotRecord,
                        ]);

                        $ownerRecord->project->update([
                            'progres' => $this->getOwnerRecord()->bobot + $this->getOwnerRecord()->project->penagihan->bobot + $this->getOwnerRecord()->project->kontrak->bobot + $this->getOwnerRecord()->project->pengiriman->bobot,
                        ]);




                        return;


                        // dd($bobotRecord, $this->getOwnerRecord()->bobot, $this->getOwnerRecord()->project->kontrak->bobot, $this->getOwnerRecord()->project->penagihan->bobot, $this->getOwnerRecord()->project->pengiriman->bobot,);
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function ($record) {
                        $ownerRecord = $this->getOwnerRecord();

                        $progres = 0;

                        $bobotRecord = 0;

                        $ownerRecord->update([
                            'progres' => $progres,
                            'bobot' => $bobotRecord,
                        ]);

                        $ownerRecord->project->update([
                            'progres' => $this->getOwnerRecord()->bobot + $this->getOwnerRecord()->project->penagihan->bobot + $this->getOwnerRecord()->project->kontrak->bobot + $this->getOwnerRecord()->project->pengiriman->bobot,
                        ]);





                        return;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
