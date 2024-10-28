<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Project;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProjectResource;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class ProjectTable extends BaseWidget
{
    use HasWidgetShield;
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 8;

    protected static ?string $pollingInterval = '10s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn(Project $query) => $query
            )
            ->paginatedWhileReordering()
            // ->poll('3s')
            ->columns([
                ViewColumn::make('progres')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->view('tables.columns.status-progres'),
                Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->alignment(Alignment::Center)
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
                Tables\Columns\TextColumn::make('garansi')
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
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Project $record): string => ProjectResource::getUrl('view', ['record' => $record])),
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
}
