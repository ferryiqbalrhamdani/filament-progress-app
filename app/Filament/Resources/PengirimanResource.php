<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Pengiriman;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Tabs;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Wizard;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\ViewField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PengirimanResource\Pages;
use App\Filament\Resources\PengirimanResource\RelationManagers;

class PengirimanResource extends Resource
{
    protected static ?string $model = Pengiriman::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // If 'pengirimanProject' is null or empty, set a default item
        if (empty($data['pengirimanProject'])) {
            $data['pengirimanProject'] = [
                [
                    'jenis_pengiriman' => null,
                    'tanggal_pengiriman' => null,
                ],
            ];
        }

        return $data;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Progres')
                    ->schema([
                        ViewField::make('progres')
                            ->disabled()
                            ->view('tables.columns.status-progres')
                    ]),
                Forms\Components\Section::make([
                    Forms\Components\Placeholder::make('project.nama_pengadaan')
                        ->content(fn(Pengiriman $record): string => $record->project->nama_pengadaan ?? '-'),
                    Forms\Components\Placeholder::make('project.no_kontrak')
                        ->content(fn(Pengiriman $record): string => $record->project->no_kontrak ?? '-'),
                    Forms\Components\Group::make([
                        Forms\Components\Placeholder::make('project.no_up')
                            ->content(fn(Pengiriman $record): string => $record->project->no_up ?? '-'),
                        Forms\Components\Placeholder::make('project.tahun_anggaran')
                            ->content(fn(Pengiriman $record): string => $record->project->tahun_anggaran ?? '-'),
                        Forms\Components\Placeholder::make('project.pic.first_name')
                            ->label('PIC')
                            ->content(fn(Pengiriman $record): string => $record->project->pic->first_name . ' ' . $record->project->pic->last_name ?? '-'),
                        Forms\Components\Placeholder::make('userInputBy.first_name')
                            ->label('Penanggung Jawab')
                            ->content(fn(Pengiriman $record): string => $record->userInputBy?->first_name  ?? '-' . ' ' . $record->userInputBy?->last_name),
                    ])
                        ->columns(4),
                ]),

                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Step 1')
                            ->schema([
                                Forms\Components\Repeater::make('pengirimanProject')
                                    ->relationship() // This assumes 'pengirimanProject' is a hasOne or belongsTo relationship
                                    ->schema([
                                        Forms\Components\Select::make('jenis_pengiriman')
                                            ->searchable()
                                            ->required()
                                            ->preload()
                                            ->options([
                                                'Lengkap' => 'Lengkap',
                                                'Bertahap' => 'Bertahap',
                                            ])
                                            ->reactive(),
                                        Forms\Components\DatePicker::make('tanggal_pengiriman')
                                            ->required(),
                                    ])
                                    ->reorderable(false)
                                    ->deletable(false)
                                    ->hiddenLabel()
                                    ->collapsible(false)
                                    ->minItems(1)
                                    ->maxItems(1)
                                    ->defaultItems(1) // Always display one item in the repeater
                                    ->columnSpanFull()
                                    ->addActionLabel('Tambah'),
                            ]),
                        Tabs\Tab::make('Step 2')
                            ->schema([
                                Forms\Components\Fieldset::make('BA Anname')
                                    ->schema([

                                        Forms\Components\Repeater::make('pengirimanBaAnname')
                                            ->relationship('pengirimanBaAnname') // This assumes 'pengirimanBaAnname' is a hasOne or belongsTo relationship
                                            ->schema([
                                                Forms\Components\TextInput::make('no_ba_anname')
                                                    ->label('No. BA Anname')
                                                    ->required()
                                                    ->inlineLabel()
                                                    ->maxLength(255),
                                                Forms\Components\DatePicker::make('tanggal_ba_anname')
                                                    ->label('Tgl. BA Anname')
                                                    ->required()
                                                    ->inlineLabel(),
                                            ])
                                            ->reorderable(false)
                                            ->hiddenLabel()
                                            ->collapsible(false)
                                            ->columnSpanFull()
                                            ->addActionLabel('Tambah')
                                            ->reactive(),
                                    ])
                                    ->columns(1),
                                Forms\Components\Fieldset::make('BA Inname')
                                    ->schema([

                                        Forms\Components\Repeater::make('pengirimanBaInname')
                                            ->relationship() // This assumes 'pengirimanBaInname' is a hasOne or belongsTo relationship
                                            ->schema([
                                                Forms\Components\TextInput::make('no_ba_inname')
                                                    ->label('No. BA Inname')
                                                    ->required()
                                                    ->inlineLabel()
                                                    ->maxLength(255),
                                                Forms\Components\DatePicker::make('tanggal_ba_inname')
                                                    ->label('Tgl. BA Inname')
                                                    ->required()
                                                    ->inlineLabel(),
                                            ])
                                            ->reorderable(false)
                                            ->hiddenLabel()
                                            ->collapsible(false)
                                            ->columnSpanFull()
                                            ->addActionLabel('Tambah'),
                                    ])
                                    ->columns(1),
                                Forms\Components\Fieldset::make('BA BAST')
                                    ->schema([
                                        Forms\Components\Repeater::make('pengirimanBast')
                                            ->relationship() // This assumes 'pengirimanBast' is a hasOne or belongsTo relationship
                                            ->schema([
                                                Forms\Components\TextInput::make('no_bast')
                                                    ->label('No. BAST')
                                                    ->required()
                                                    ->inlineLabel()
                                                    ->maxLength(255),
                                                Forms\Components\DatePicker::make('tanggal_bast')
                                                    ->label('Tgl. BAST')
                                                    ->required()
                                                    ->inlineLabel(),
                                            ])
                                            ->reorderable(false)
                                            ->hiddenLabel()
                                            ->collapsible(false)
                                            ->maxItems(1)
                                            ->defaultItems(1) // Always display one item in the repeater
                                            ->columnSpanFull()
                                            ->addActionLabel('Tambah'),
                                    ]),
                            ]),

                    ])
                    ->columnSpanFull(),

                // Wizard::make([
                //     Wizard\Step::make('Step 1')
                //         ->schema([
                //             Forms\Components\Repeater::make('pengirimanProject')
                //                 ->relationship() // This assumes 'pengirimanProject' is a hasOne or belongsTo relationship
                //                 ->schema([
                //                     Forms\Components\Select::make('jenis_pengiriman')
                //                         ->searchable()
                //                         ->preload()
                //                         ->options([
                //                             'Lengkap' => 'Lengkap',
                //                             'Bertahap' => 'Bertahap',
                //                         ])
                //                         ->live()
                //                         ->dehydrated(fn($state) => $state !== 'Bertahap'),
                //                     Forms\Components\DatePicker::make('tanggal_pengiriman'),
                //                 ])
                //                 ->reorderable(false)
                //                 ->deletable(false)
                //                 ->hiddenLabel()
                //                 ->collapsible(false)
                //                 ->minItems(1)
                //                 ->maxItems(1)
                //                 ->defaultItems(1) // Always display one item in the repeater
                //                 ->columnSpanFull()
                //                 ->addActionLabel('Tambah'),
                //         ]),
                //     Wizard\Step::make('Step 2')
                //         ->schema([
                //             Forms\Components\Fieldset::make('BA Anname')
                //                 ->schema([

                //                     Forms\Components\Repeater::make('pengirimanBaAnname')
                //                         ->relationship() // This assumes 'pengirimanBaAnname' is a hasOne or belongsTo relationship
                //                         ->schema([
                //                             Forms\Components\TextInput::make('no_ba_anname')
                //                                 ->label('No. BA Anname')
                //                                 ->inlineLabel()
                //                                 ->maxLength(255),
                //                             Forms\Components\DatePicker::make('tanggal_ba_anname')
                //                                 ->label('Tgl. BA Anname')
                //                                 ->inlineLabel(),
                //                         ])
                //                         ->reorderable(false)
                //                         ->hiddenLabel()
                //                         ->collapsible(false)
                //                         ->columnSpanFull()
                //                         ->addActionLabel('Tambah')
                //                         ->maxItems(function (Get $get) {
                //                             if ($get('jenis_pengiriman') === 'Lengkap') {
                //                                 return 1;
                //                             } else {
                //                                 return null;
                //                             }
                //                         }),
                //                 ])
                //                 ->columns(1),
                //             Forms\Components\Fieldset::make('BA Inname')
                //                 ->schema([

                //                     Forms\Components\Repeater::make('pengirimanBaInname')
                //                         ->relationship() // This assumes 'pengirimanBaInname' is a hasOne or belongsTo relationship
                //                         ->schema([
                //                             Forms\Components\TextInput::make('no_ba_inname')
                //                                 ->label('No. BA Inname')
                //                                 ->inlineLabel()
                //                                 ->maxLength(255),
                //                             Forms\Components\DatePicker::make('tanggal_ba_inname')
                //                                 ->label('Tgl. BA Inname')
                //                                 ->inlineLabel(),
                //                         ])
                //                         ->reorderable(false)
                //                         ->hiddenLabel()
                //                         ->collapsible(false)
                //                         ->columnSpanFull()
                //                         ->addActionLabel('Tambah')
                //                         ->maxItems(function (Get $get) {
                //                             if ($get('jenis_pengiriman') === 'Lengkap') {
                //                                 return 1;
                //                             } else {
                //                                 return null;
                //                             }
                //                         }),
                //                 ])
                //                 ->columns(1),
                //             Forms\Components\Fieldset::make('BA BAST')
                //                 ->schema([
                //                     Forms\Components\Repeater::make('pengirimanBast')
                //                         ->relationship() // This assumes 'pengirimanBast' is a hasOne or belongsTo relationship
                //                         ->schema([
                //                             Forms\Components\TextInput::make('no_bast')
                //                                 ->label('No. BAST')
                //                                 ->inlineLabel()
                //                                 ->maxLength(255),
                //                             Forms\Components\DatePicker::make('tanggal_bast')
                //                                 ->label('Tgl. BAST')
                //                                 ->inlineLabel(),
                //                         ])
                //                         ->reorderable(false)
                //                         ->deletable(false)
                //                         ->hiddenLabel()
                //                         ->collapsible(false)
                //                         ->minItems(1)
                //                         ->maxItems(1)
                //                         ->defaultItems(1) // Always display one item in the repeater
                //                         ->columnSpanFull()
                //                         ->addActionLabel('Tambah'),
                //                 ]),

                //         ]),

                // ])
                //     ->columnSpanFull(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            // ->query(
            //     fn(Pengiriman $query) => $query->whereHas('project', function (Builder $query) {
            //         return $query->whereNull('deleted_at');
            //     })
            // )
            ->columns([
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
                    ->alignment(Alignment::Center)
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
                            $indicators['tanggal_dari'] = 'Tanggal Mulai: ' . Carbon::parse($data['tanggal_dari'])->toFormattedDateString();
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators['sampai_tanggal'] = 'Tanggal Akhir: ' . Carbon::parse($data['sampai_tanggal'])->toFormattedDateString();
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
                    ->visible(fn($record) => $record->user_input_by == Auth::user()->id || $record->user_input_by == NULL && $record->project->status === false),
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
            'index' => Pages\ListPengirimen::route('/'),
            'create' => Pages\CreatePengiriman::route('/create'),
            'edit' => Pages\EditPengiriman::route('/{record}/edit'),
        ];
    }
}
