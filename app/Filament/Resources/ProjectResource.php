<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Project;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Forms\Form;
use App\Models\AsalBrand;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use App\Models\AsalBrandKhusus;
use App\Models\SertifikatProduk;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Grouping\Group;
use Infolists\Components\TextEntry;
use Filament\Forms\Components\Wizard;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Infolists\Components\Split;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Filament\Resources\ProjectResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProjectResource\RelationManagers;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Filament\Infolists\Components\TextEntry as ComponentsTextEntry;
use App\Filament\Resources\ProjectResource\Widgets\ProjectsOverview;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $recordTitleAttribute = 'nama_pengadaan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Step 1')
                            ->icon('heroicon-m-clipboard')
                            ->schema([
                                Forms\Components\TextInput::make('nama_pengadaan')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('no_up')
                                    ->required()
                                    ->unique(Project::class, 'no_up', ignoreRecord: true)
                                    ->label('No. UP')
                                    ->maxLength(255),
                                Forms\Components\Select::make('jenis_lelang_id')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->relationship(name: 'jenisLelang', titleAttribute: 'name'),
                                Forms\Components\Select::make('instansi_id')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->relationship(name: 'instansi', titleAttribute: 'name'),
                                Forms\Components\Select::make('company_id')
                                    ->required()
                                    ->label('Perusahaan')
                                    ->searchable()
                                    ->preload()
                                    ->relationship(name: 'company', titleAttribute: 'name'),
                                Forms\Components\Select::make('jenis_anggaran_id')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->relationship(name: 'jenisAnggaran', titleAttribute: 'name'),
                                Forms\Components\TextInput::make('tahun_anggaran')
                                    ->required()
                                    ->numeric()
                                    ->maxLength(255),
                                Forms\Components\Select::make('pic_id')
                                    ->required()
                                    ->label('PIC')
                                    ->relationship(
                                        name: 'pic',
                                        modifyQueryUsing: fn(Builder $query) => $query->orderBy('first_name')->orderBy('last_name'),
                                    )
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->first_name} {$record->last_name}")
                                    ->searchable(['first_name', 'last_name'])
                                    ->preload(),
                                Forms\Components\Select::make('vendor_id')
                                    ->searchable()
                                    ->required()
                                    ->multiple()
                                    ->preload()
                                    ->relationship(name: 'vendors', titleAttribute: 'name'),
                                Forms\Components\Textarea::make('deskripsi')
                                    ->rows(7)
                                    ->cols(7)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Step 2')
                            ->icon('heroicon-m-clipboard')
                            ->schema([
                                Forms\Components\Split::make([
                                    Forms\Components\Fieldset::make('Bebas Pajak?')
                                        ->schema([
                                            Forms\Components\Radio::make('bebas_pajak')
                                                ->inline()
                                                ->required()
                                                ->boolean()
                                                ->inlineLabel(false)
                                                ->reactive()
                                                ->hiddenLabel(),

                                            Forms\Components\Radio::make('bebas_pajak_khusus')
                                                ->inline()
                                                ->required()
                                                ->inlineLabel(false)
                                                ->options([
                                                    'SKTD' => 'SKTD',
                                                    'SKB' => 'SKB',
                                                ])
                                                ->hiddenLabel()
                                                ->hidden(function (callable $get) {
                                                    if ($get('bebas_pajak') == true) {
                                                        return false;
                                                    } else {
                                                        return true;
                                                    }
                                                }),
                                        ]),
                                    Forms\Components\Fieldset::make('Asal Brand')
                                        ->schema([
                                            Forms\Components\Radio::make('asal_brand_id')
                                                ->label('Asal Brand')
                                                ->options(AsalBrand::all()->pluck('name', 'id'))
                                                ->reactive() // Makes the form update when the radio button changes
                                                ->hiddenLabel()
                                                ->required()
                                                ->inline()
                                                ->inlineLabel(false),

                                            Forms\Components\Radio::make('asal_brand_khusus')
                                                ->inline()
                                                ->required()
                                                ->inlineLabel(false)
                                                ->options([
                                                    'SP2' => 'SP2',
                                                    'Non SP2' => 'Non SP2',
                                                ])
                                                ->hiddenLabel()
                                                ->visible(fn(callable $get) => $get('asal_brand_id') == AsalBrand::where('name', 'Import')->first()->id),



                                        ]),
                                ]),

                                Forms\Components\Fieldset::make('Sertifikat Produk')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('sertifikatProduk')
                                            ->label('Sertifikat Produk')
                                            ->required()
                                            ->relationship(
                                                titleAttribute: 'name',
                                                modifyQueryUsing: function (Builder $query, callable $get) {
                                                    // Get the selected 'asal_brand_id'
                                                    $asalBrandId = $get('asal_brand_id');

                                                    // Modify the query to only fetch the sertifikat_produk related to the selected asal_brand
                                                    if ($asalBrandId) {
                                                        $query->where('asal_brand_id', $asalBrandId);
                                                    }

                                                    return $query;
                                                }
                                            )
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                // This will be executed whenever the checkbox list is updated
                                                $set('sertifikatProduk', $state);
                                            })
                                            ->hiddenLabel()
                                            ->visible(fn(Get $get) => $get('asal_brand_id') !== NULL),

                                    ]),

                                Forms\Components\Split::make([
                                    Forms\Components\Fieldset::make('Payment Term')
                                        ->schema([
                                            Forms\Components\Radio::make('payment_term')
                                                ->options([
                                                    'Tidak ada DP' => 'Tidak ada DP',
                                                    'DP' => 'DP 20%',
                                                    'Termin' => 'Termin',
                                                ])
                                                ->required()
                                                ->reactive()
                                                ->inline()
                                                ->hiddenLabel(),
                                        ]),

                                    Forms\Components\Fieldset::make('Garansi?')
                                        ->schema([
                                            Forms\Components\Radio::make('garansi')
                                                ->boolean()
                                                ->required()
                                                ->inline()
                                                ->reactive()
                                                ->inlineLabel(false)
                                                ->hiddenLabel(),

                                            Forms\Components\Group::make([
                                                Forms\Components\TextInput::make('lama_garansi')
                                                    ->hiddenLabel()
                                                    ->required()
                                                    ->helperText('Isi lama garansi dalam angka')
                                                    ->minValue(1)
                                                    ->suffix('Hari')
                                                    ->mask(RawJs::make('$money($input)'))
                                                    ->stripCharacters(',')
                                                    ->numeric(),
                                            ])
                                                ->relationship('lamaGaransi')
                                                ->hidden(function (callable $get) {
                                                    if ($get('garansi') == true) {
                                                        return false;
                                                    } else {
                                                        return true;
                                                    }
                                                }),
                                        ])
                                        ->columns(1),
                                ]),
                            ]),
                        Tabs\Tab::make('Step 3')
                            ->icon('heroicon-m-clipboard')
                            ->schema([
                                Forms\Components\TextInput::make('no_kontrak')
                                    ->required()
                                    ->unique(Project::class, 'no_kontrak', ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('nilai_kontrak')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->required()
                                    ->live(onBlur: true)
                                    ->dehydrated()
                                    ->stripCharacters(',')
                                    ->prefix('Rp ')
                                    ->numeric()
                                    ->default(0)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        self::updateTotalItem($state, $get, $set);
                                    }),
                                Forms\Components\DatePicker::make('tanggal_kontrak')
                                    ->required(),
                                Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                                    ->required(),

                                // Fieldset for Termin
                                Forms\Components\Fieldset::make('Termin')
                                    ->schema([
                                        self::getItemsRepeater(),
                                    ])
                                    ->visible(fn(callable $get) => $get('payment_term') === 'Termin')
                                    ->columnSpanFull(),

                                // Fieldset for DP
                                Forms\Components\Fieldset::make('DP')
                                    ->schema([
                                        Forms\Components\Hidden::make('dp_total')
                                            ->required()
                                            ->hiddenLabel(),
                                        Forms\Components\Placeholder::make('total')
                                            ->content(function (Get $get) {
                                                $cleanedState = str_replace(',', '', $get('nilai_kontrak')); // Remove commas
                                                $nilaiKontrak = (float)$cleanedState; // Convert to float
                                                return 'Rp ' . number_format((float)$nilaiKontrak * 0.2, 2);
                                            })
                                            ->extraAttributes(['class' => 'text-9xl font-bold'])
                                            ->hiddenLabel(),
                                    ])
                                    ->visible(fn(callable $get) => $get('payment_term') === 'DP'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),


            ]);
    }

    protected static function updateTotalItem($state, Forms\Get $get, Forms\Set $set): void
    {
        // Remove commas from the value and convert it to a float
        $cleanedState = str_replace(',', '', $state); // Remove commas
        $nilaiKontrak = (float)$cleanedState; // Convert to float

        // Debugging output
        // dd($cleanedState, $nilaiKontrak);
        if ($get('payment_term') === 'DP') {
            // Calculate 20% of nilai_kontrak
            $nilaiKontrakData = (float) $nilaiKontrak * 0.2;

            // Update the dp_total field with the formatted DP amount
            $set('dp_total', $nilaiKontrakData);
        } else {
            // If not 'DP', set dp_total to 0
            $set('dp_total', 0);
        }
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('termins')
            ->relationship()
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('name')
                        ->default('Termin 1')
                        ->required()
                        ->hiddenLabel(),
                    Forms\Components\TextInput::make('total')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->required()
                        ->numeric()
                        ->prefix('Rp ')
                        ->hiddenLabel(),
                ])
                    ->columns(2),
            ])
            ->reorderable(false)
            ->hiddenLabel()
            ->collapsible()
            ->defaultItems(1)
            ->columnSpanFull()
            ->addActionLabel('Tambah termin');
    }


    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                ViewColumn::make('progres')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->view('tables.columns.status-progres'),
                Tables\Columns\ToggleColumn::make('status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_pengadaan')
                    ->description(fn(Model $record) => $record->no_kontrak)
                    ->words(5)
                    ->sortable()
                    ->searchable(['nama_pengadaan', 'no_kontrak']),
                Tables\Columns\TextColumn::make('no_up')
                    ->label('No. UP')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pic.first_name')
                    ->label('PIC')
                    ->searchable()
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilai_kontrak')
                    ->searchable()
                    ->numeric()
                    ->money('IDR', locale: 'id')
                    ->summarize(
                        Sum::make()
                            ->label('Total nilai kontrak')
                            ->money('IDR', locale: 'id')
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_kontrak')
                    ->searchable()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->searchable()
                    ->date()
                    ->sortable(),
                // Tables\Columns\IconColumn::make('garansi')
                //     ->boolean()
                //     ->alignment(Alignment::Center)
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('jenisLelang.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('jenisAnggaran.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vendors.name')
                    ->searchable()
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('bebas_pajak')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_term')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
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
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_kontrak', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_kontrak', '<=', $date),
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
                                fn(Builder $query): Builder => $query->where('status', false)->whereYear('tanggal_jatuh_tempo', Carbon::now()->year),
                            )
                            ->when(
                                $data['tanggal_jatuh_tempo'] === 'semua_tahun',
                                fn(Builder $query): Builder => $query->where('status', false),
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
            ->recordAction(null)
            ->recordUrl(null)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('klaim_garansi')
                        ->label('Klaim Garansi')
                        // ->color('success')
                        ->icon('heroicon-o-check-badge')
                        ->form([
                            Forms\Components\Section::make([
                                Forms\Components\DatePicker::make('tanggal_klaim'),
                            ]),
                        ])
                        ->visible(fn(Project $record): bool => $record->garansi == true),
                    Tables\Actions\DeleteAction::make(),

                ])
                    ->link()
                    ->label('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Group::make('pic.first_name')
                    ->label('PIC')
                    ->collapsible(),
                Group::make('company.name')
                    ->label('Perusahaan')
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
            'view' => Pages\ViewProject::route('/{record}'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('progres')
                    ->schema([
                        Infolists\Components\ViewEntry::make('progres')
                            ->view('infolists.components.progres')
                            ->hiddenLabel(),
                    ]),
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('no_kontrak')
                                            ->copyable()
                                            ->copyMessage('Copied!')
                                            ->copyMessageDuration(1500),
                                        Infolists\Components\TextEntry::make('no_up')
                                            ->label('No. UP'),
                                    ])
                                    ->columns(2),
                            ]),
                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('tahun_anggaran'),
                                        Infolists\Components\TextEntry::make('company.name')
                                            ->label('Perusahaan')
                                            ->badge(),
                                        Infolists\Components\TextEntry::make('jenisLelang.name'),
                                        Infolists\Components\TextEntry::make('instansi.name'),
                                        Infolists\Components\TextEntry::make('pic.first_name')
                                            ->label('PIC')
                                            ->badge(),
                                        Infolists\Components\TextEntry::make('jenisAnggaran.name'),
                                        Infolists\Components\TextEntry::make('vendors.name')
                                            ->listWithLineBreaks()
                                            ->bulleted()
                                            ->label('Vendor'),
                                        Infolists\Components\TextEntry::make('deskripsi'),
                                    ])
                                    ->columns(3),
                            ]),

                    ])
                    ->columns(1),
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\IconEntry::make('bebas_pajak')
                                            ->label('Bebas pajak?')
                                            ->boolean(),
                                        Infolists\Components\TextEntry::make('bebas_pajak_khusus')
                                            ->label('Jenis pajak')
                                            ->visible(
                                                fn($record) => $record->bebas_pajak === true
                                            ),
                                    ])
                                    ->columns(2),
                            ]),
                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('asalBrand.name'),
                                        Infolists\Components\TextEntry::make('asal_brand_khusus')
                                            ->label('Jenis asal brand')
                                            ->visible(
                                                fn($record) => optional($record->asalBrand)->name === 'Import'
                                            ),
                                    ])
                                    ->columns(2),
                            ]),

                        Infolists\Components\Section::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('sertifikatProduk')
                                    ->label('Sertifikat Produk')
                                    ->getStateUsing(function ($record) {
                                        // Dapatkan sertifikat produk yang sesuai dengan asal_brand_id
                                        return $record->sertifikatProduk()
                                            ->where('asal_brand_id', $record->asal_brand_id)
                                            ->pluck('name')
                                            ->toArray();
                                    })
                                    ->listWithLineBreaks() // Atau ->bulleted() jika ingin tampilannya berupa list dengan bullet
                                    ->visible(fn($record) => $record->asal_brand_id !== null)
                                    ->listWithLineBreaks()
                                    ->bulleted(),
                            ]),
                        Infolists\Components\Section::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('payment_term'),
                            ]),
                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\IconEntry::make('garansi')
                                            ->label('Garansi?')
                                            ->boolean(),
                                        Infolists\Components\TextEntry::make('lamaGaransi.lama_garansi')
                                            ->suffix(' Hari')
                                            ->visible(
                                                fn($record) => $record->garansi === true
                                            ),
                                    ])
                                    ->columns(2),
                            ]),
                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\IconEntry::make('claim_garansi')
                                            ->boolean()
                                            ->default(false),
                                    ]),
                            ]),

                    ])
                    ->columns(2),
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('nilai_kontrak')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('tanggal_kontrak')
                            ->date(),
                        Infolists\Components\TextEntry::make('tanggal_jatuh_tempo')
                            ->date(),

                    ])
                    ->columns(3),
                Infolists\Components\Section::make('DP')
                    ->schema([
                        Infolists\Components\TextEntry::make('projectDp.dp_total')
                            ->hiddenLabel()
                            ->money('IDR')
                    ])
                    ->visible(
                        fn($record) => $record->payment_term === 'DP'
                    ),
                Infolists\Components\Section::make('Termin')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('termins')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->money('IDR')
                                    ->hiddenLabel(),
                                Infolists\Components\TextEntry::make('total')
                                    ->money('IDR')
                                    ->hiddenLabel(),
                            ])
                            ->hiddenLabel()
                            ->columns(2)
                    ])
                    ->visible(
                        fn($record) => $record->payment_term === 'Termin'
                    ),

                Infolists\Components\Section::make('Kontrak')
                    ->collapsible()
                    ->schema([
                        Infolists\Components\ViewEntry::make('kontrak.progres')
                            ->view('infolists.components.progres')
                            ->hiddenLabel(),
                        Infolists\Components\Section::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('Nama Kontrak'),
                                Infolists\Components\TextEntry::make('Status Kontrak'),
                                Infolists\Components\TextEntry::make('Tgl Input')
                                    ->label('Tgl. Input'),

                            ])
                            ->columns(3),
                        Infolists\Components\RepeatableEntry::make('kontrak.kontrakProject')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->hiddenLabel(),
                                Infolists\Components\IconEntry::make('status')
                                    ->boolean()
                                    ->hiddenLabel(),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->hiddenLabel()
                                    ->date()
                                    ->visible(fn($record) => $record->status ? true : false),
                            ])
                            ->hiddenLabel()
                            ->columns(3)

                    ]),
                Infolists\Components\Section::make('Marcendiser')
                    ->collapsible()
                    ->schema([
                        Infolists\Components\ViewEntry::make('marcendiser.progres')
                            ->view('infolists.components.progres')
                            ->hiddenLabel(),
                        Infolists\Components\Section::make([
                            Infolists\Components\TextEntry::make('pic.first_name')
                                ->label('Penanggung Jawab')
                                ->badge(),
                        ]),
                        Split::make([
                            Infolists\Components\Section::make('Barang di Kontrak')
                                ->schema([
                                    Split::make([
                                        Infolists\Components\TextEntry::make('marcendiser.jumlah_item')
                                            ->label('Jumlah Item')
                                            ->numeric(),
                                        Infolists\Components\TextEntry::make('marcendiser.jumlah_ea')
                                            ->label('Jumlah EA')
                                            ->numeric(),
                                    ]),
                                ]),
                            Infolists\Components\Section::make('Barang di Terima')
                                ->schema([
                                    Split::make([
                                        Infolists\Components\TextEntry::make('marcendiser.receiveds.jumlah_item')
                                            ->label('Jumlah Item')
                                            ->numeric(),
                                        Infolists\Components\TextEntry::make('marcendiser.receiveds.jumlah_ea')
                                            ->label('Jumlah EA')
                                            ->numeric(),
                                    ]),
                                ]),
                        ]),
                    ]),
                Infolists\Components\Section::make('Pengiriman')
                    ->collapsible()
                    ->schema([
                        Infolists\Components\ViewEntry::make('pengiriman.progres')
                            ->view('infolists.components.progres')
                            ->hiddenLabel(),
                        Infolists\Components\Section::make([
                            Infolists\Components\TextEntry::make('pengiriman.userInputBy.first_name')
                                ->label('Penanggung Jawab')
                                ->badge(),
                        ]),
                        Infolists\Components\RepeatableEntry::make('pengiriman.pengirimanBaAnname')
                            ->schema([
                                Infolists\Components\TextEntry::make('no_ba_anname')
                                    ->label('No BA Anname'),
                                Infolists\Components\TextEntry::make('tanggal_ba_anname')
                                    ->label('Tgl. BA Anname')
                                    ->date(),
                                Infolists\Components\IconEntry::make('status')
                                    ->boolean()
                                    ->default(fn($record) => $record ? true : false)
                                    ->label('Status'),
                            ])
                            ->hiddenLabel()
                            ->columns(3),
                        Infolists\Components\RepeatableEntry::make('pengiriman.pengirimanBaInname')
                            ->schema([
                                Infolists\Components\TextEntry::make('no_ba_inname')
                                    ->label('No BA Inname'),
                                Infolists\Components\TextEntry::make('tanggal_ba_inname')
                                    ->label('Tgl. BA Inname')
                                    ->date(),
                                Infolists\Components\IconEntry::make('status')
                                    ->boolean()
                                    ->default(fn($record) => $record ? true : false)
                                    ->label('Status'),
                            ])
                            ->hiddenLabel()
                            ->columns(3),
                        Infolists\Components\Section::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('pengiriman.pengirimanBast.no_bast')
                                    ->label('No BAST'),
                                Infolists\Components\TextEntry::make('pengiriman.pengirimanBast.tanggal_bast')
                                    ->label('Tgl. BAST')
                                    ->date(),
                                Infolists\Components\IconEntry::make('pengiriman.pengirimanBast')
                                    ->boolean()
                                    ->default(fn($record) => $record->no_bast ? true : false)
                                    ->label('Status'),
                            ])
                            ->columns(3),

                    ]),
                Infolists\Components\Section::make('Penagihan')
                    ->collapsible()
                    ->schema([
                        Infolists\Components\ViewEntry::make('penagihan.progres')
                            ->view('infolists.components.progres')
                            ->hiddenLabel(),
                        Infolists\Components\Section::make([
                            Infolists\Components\TextEntry::make('penagihan.userInputBy.first_name')
                                ->label('Penanggung Jawab')
                                ->badge(),
                        ]),
                        Infolists\Components\Section::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('Nama Penagihan'),
                                Infolists\Components\TextEntry::make('Jenis Penagihan'),
                                Infolists\Components\TextEntry::make('Status Penagihan'),
                                Infolists\Components\TextEntry::make('Tgl Input')
                                    ->label('Tgl. Input'),

                            ])
                            ->columns(4),
                        Infolists\Components\RepeatableEntry::make('penagihan.penagihanProject')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->hiddenLabel(),
                                Infolists\Components\TextEntry::make('jenis_penagihan')
                                    ->hiddenLabel(),
                                Infolists\Components\IconEntry::make('status')
                                    ->alignment(Alignment::Center)
                                    ->boolean()
                                    ->hiddenLabel(),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->hiddenLabel()
                                    ->date()
                                    ->visible(fn($record) => $record->status ? true : false),
                            ])
                            ->hiddenLabel()
                            ->columns(4)
                    ]),
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['no_kontrak', 'nama_pengadaan', 'no_up'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Product $record */

        return [
            $record->no_up,
            $record->no_kontrak,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ProjectsOverview::class,
        ];
    }
}
