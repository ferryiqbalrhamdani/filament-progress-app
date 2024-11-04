<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Marcendiser;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\ViewField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MarcendiserResource\Pages;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Resources\MarcendiserResource\RelationManagers;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class MarcendiserResource extends Resource
{
    protected static ?string $model = Marcendiser::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $recordTitleAttribute = 'project.nama_pengadaan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Section::make('Progres')
                //     ->schema([
                //         ViewField::make('progres')
                //             ->view('tables.columns.status-progres')
                //     ]),
                Forms\Components\Section::make([
                    Forms\Components\Placeholder::make('project.nama_pengadaan')
                        ->content(fn(Marcendiser $record): string => $record->project->nama_pengadaan),
                    Forms\Components\Group::make([
                        Forms\Components\Placeholder::make('project.no_kontrak')
                            ->content(fn(Marcendiser $record): string => $record->project->no_kontrak),
                        Forms\Components\Placeholder::make('project.jenisAnggaran.name')
                            ->content(fn(Marcendiser $record): string => $record->project->jenisAnggaran->name)
                            ->label('Jenis Anggaran'),
                    ])
                        ->columns(2),
                    Forms\Components\Group::make([
                        Forms\Components\Placeholder::make('project.no_up')
                            ->content(fn(Marcendiser $record): string => $record->project->no_up),
                        Forms\Components\Placeholder::make('project.tahun_anggaran')
                            ->content(fn(Marcendiser $record): string => $record->project->tahun_anggaran),
                        Forms\Components\Placeholder::make('project.pic.first_name')
                            ->label('PIC')
                            ->content(fn(Marcendiser $record): string => $record->project->pic->first_name . ' ' . $record->project->pic->last_name),
                    ])
                        ->columns(3),
                ]),

                Forms\Components\Section::make('Barang di Kontrak')
                    ->description('Input dan simpan barang di kontrak terlebih dahulu untuk melanjutkan proses.')
                    ->schema([
                        Forms\Components\TextInput::make('jumlah_item')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->required()
                            ->maxLength(255)
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('jumlah_ea')
                            ->label('Jumlah EA')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->required()
                            ->maxLength(255)
                            ->inlineLabel(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
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
                Tables\Columns\TextColumn::make('project.jenisAnggaran.name')
                    ->label('Jenis Anggaran')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('project.pic.first_name')
                    ->label('PIC')
                    ->searchable()
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_ea')
                    ->label('Barang Dikontrak')
                    ->badge()
                    ->default(0)
                    ->alignment(Alignment::Center)
                    // ->description(function (Marcendiser $record) {
                    //     return 'Barang Diterima: ' . $record->receiveds->sum('jumlah_ea');
                    // })
                    ->color('warning')
                    ->numeric()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('receiveds.jumlah_ea')
                    ->label('Barang Diterima')
                    ->default(0)
                    ->numeric()
                    ->badge()
                    ->alignment(Alignment::Center)
                    ->color('success')
                    ->searchable()
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
                    ->visible(fn($record) => $record->project->status === false),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(
                fn(Marcendiser $query) => $query->whereHas('project', function (Builder $query) {
                    if (Auth::user()->roles->contains('name', 'super_admin')) {
                        return $query;
                    }

                    return $query->where('pic_id', Auth::id());
                })
            );
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Kontrak', [
                RelationManagers\PosRelationManager::class,
                RelationManagers\InvoicesRelationManager::class,
            ])
                ->icon('heroicon-m-clipboard-document-list'),
            RelationGroup::make('Shipping Barang', [
                RelationManagers\ProductionsRelationManager::class,
                RelationManagers\DeliveriesRelationManager::class,
                RelationManagers\ReadyStocksRelationManager::class,
                RelationManagers\ReceivedsRelationManager::class,
            ])
                ->icon('heroicon-m-truck'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarcendisers::route('/'),
            'create' => Pages\CreateMarcendiser::route('/create'),
            'edit' => Pages\EditMarcendiser::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['project', 'pos']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['project.no_kontrak', 'project.nama_pengadaan', 'project.no_up', 'pos.no_po'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $noPoValues = $record->pos->pluck('no_po')->toArray();
        return [
            'Nama' => $record->project->nama_pengadaan,
            'No. Kontrak' => $record->project->no_kontrak,
            'No. UP' => $record->project->no_up,
            'No. PO' => implode(', ', $noPoValues),
        ];
    }
}
