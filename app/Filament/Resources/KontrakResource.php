<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kontrak;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\ViewField;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\KontrakResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KontrakResource\RelationManagers;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class KontrakResource extends Resource
{
    protected static ?string $model = Kontrak::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Progres')
                            ->schema([
                                ViewField::make('progres')
                                    ->view('tables.columns.status-progres')
                            ]),
                        Forms\Components\Section::make([
                            Forms\Components\Placeholder::make('project.nama_pengadaan')
                                ->content(fn(kontrak $record): string => $record->project->nama_pengadaan),
                            Forms\Components\Placeholder::make('project.no_kontrak')
                                ->content(fn(kontrak $record): string => $record->project->no_kontrak),
                            Forms\Components\Group::make([
                                Forms\Components\Placeholder::make('project.no_up')
                                    ->content(fn(kontrak $record): string => $record->project->no_up),
                                Forms\Components\Placeholder::make('project.tahun_anggaran')
                                    ->content(fn(kontrak $record): string => $record->project->tahun_anggaran),
                                Forms\Components\Placeholder::make('project.pic.first_name')
                                    ->label('PIC')
                                    ->content(fn(kontrak $record): string => $record->project->pic->first_name . ' ' . $record->project->pic->last_name),
                            ])
                                ->columns(3),
                        ]),
                        self::getItemsRepeater(),
                    ])
                    ->columnSpan(['lg' => 3]),
            ])
            ->columns(3)
        ;
    }

    public static function getItemsRepeater(): TableRepeater
    {
        return TableRepeater::make('kontrakProject')
            ->relationship('kontrakProject')
            ->schema([

                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->readonly(),
                Forms\Components\Toggle::make('status')
                    ->inline(false)
                    ->label('Selesai?')
                    ->onColor('success')
                    ->offColor('danger'),

            ])
            ->reorderable()
            ->cloneable()
            ->collapsible()
            ->defaultItems(1)
            ->columnSpanFull()
            ->addActionLabel('Tambah');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            // ->query(
            //     fn(Kontrak $query) => $query->whereHas('project', function (Builder $query) {
            //         return $query->whereNull('deleted_at');
            //     })
            // )
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                ViewColumn::make('progres')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->view('tables.columns.status-progres'),
                Tables\Columns\IconColumn::make('project.status')
                    ->label('Status')
                    ->boolean()
                    ->alignment(Alignment::Center)
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.nama_pengadaan')
                    ->description(fn(Model $record) => $record->project->no_kontrak)
                    ->words(5)
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->whereHas('project', function (Builder $query) use ($search) {
                                $lowerSearch = strtolower($search);
                                return $query
                                    ->whereRaw('LOWER(nama_pengadaan) LIKE ?', ["%{$lowerSearch}%"])
                                    ->orWhereRaw('LOWER(no_kontrak) LIKE ?', ["%{$lowerSearch}%"])
                                    ->whereNull('deleted_at');
                            });
                    }),
                Tables\Columns\TextColumn::make('project.no_up')
                    ->label('No. UP')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('project.pic.first_name')
                    ->label('PIC')
                    ->searchable()
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userInputBy.first_name')
                    ->label('Penanggung Jawab')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->badge()
                    ->sortable()
                    ->getStateUsing(function (Model $record): string {
                        // Cek apakah userInputBy tidak null sebelum mengakses first_name dan last_name
                        if ($record->userInputBy) {
                            return $record->userInputBy->first_name . ' ' . $record->userInputBy->last_name;
                        }

                        // Jika userInputBy null, tampilkan '-'
                        return '-';
                    }),
                Tables\Columns\TextColumn::make('project.tanggal_kontrak')
                    ->label('Tanggal Kontrak')
                    ->searchable()
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('project.tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->searchable()
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->filters([
                Tables\Filters\Filter::make('tanggal_kontrak')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari')
                            ->label('Tanggal Mulai Kontrak')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Tanggal Akhir Kontrak')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereHas('project', function (Builder $query) use ($date) {
                                    $query->whereDate('tanggal_kontrak', '>=', $date);
                                }),
                            )
                            ->when(
                                $data['sampai_tanggal'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereHas('project', function (Builder $query) use ($date) {
                                    $query->whereDate('tanggal_kontrak', '<=', $date);
                                }),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['tanggal_dari'] ?? null) {
                            $indicators['tanggal_dari'] = 'Tanggal Mulai Kontrak: ' . Carbon::parse($data['tanggal_dari'])->toFormattedDateString();
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators['sampai_tanggal'] = 'Tanggal Akhir Kontrak: ' . Carbon::parse($data['sampai_tanggal'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('Tahun')
                    ->form([
                        Forms\Components\Select::make('tanggal_jatuh_tempo')
                            ->label('Jatuh Tempo')
                            ->options([
                                'semua_tahun' => 'Semua Tahun',
                                'tahun_ini' => 'Tahun Ini',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_jatuh_tempo'] === 'tahun_ini',
                                fn(Builder $query): Builder => $query->whereHas('project', function (Builder $query) {
                                    $query->where('status', false)
                                        ->whereYear('tanggal_jatuh_tempo', Carbon::now()->year);
                                }),
                            )
                            ->when(
                                $data['tanggal_jatuh_tempo'] === 'semua_tahun',
                                fn(Builder $query): Builder => $query->whereHas('project', function (Builder $query) {
                                    $query->where('status', false);
                                }),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['tanggal_jatuh_tempo'] === 'tahun_ini') {
                            $indicators['tanggal_jatuh_tempo'] = 'Jatuh Tempo: ' . Carbon::now()->year;
                        } elseif ($data['tanggal_jatuh_tempo'] === 'semua_tahun') {
                            $indicators['tanggal_jatuh_tempo'] = 'Jatuh Tempo: Semua Tahun';
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => fn($record) => $record->user_input_by == Auth::user()->id || $record->project->status === false),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKontraks::route('/'),
            'create' => Pages\CreateKontrak::route('/create'),
            'edit' => Pages\EditKontrak::route('/{record}/edit'),
        ];
    }
}
