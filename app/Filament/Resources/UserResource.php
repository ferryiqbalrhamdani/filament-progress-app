<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Enum\GenderType;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\Widgets\UsersOverview;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Pengaturan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nama Depan')
                            ->placeholder('Jhon')
                            ->inlineLabel()
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->afterStateHydrated(fn($set, $get) => self::generateUsername($set, $get))
                            ->afterStateUpdated(fn($set, $get) => self::generateUsername($set, $get)),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nama Belakang')
                            ->placeholder('Albert Doe')
                            ->inlineLabel()
                            ->helperText('optional')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->inlineLabel()
                            ->helperText('Username akan otomatis dibuat')
                            ->unique(User::class, 'username', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->inlineLabel()
                            ->default('password')
                            ->required()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('By default password is "password"')
                            ->visibleOn('create'),
                        Forms\Components\Radio::make('jk')
                            ->label('Jenis Kelamin')
                            ->inlineLabel()
                            ->inline()
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->default('Laki-laki')
                            ->required(),
                        Forms\Components\Toggle::make('status')
                            ->onColor('success')
                            ->offColor('danger')
                            ->label('Status Akun')
                            ->inlineLabel()
                            ->default(true)
                            ->required(),
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name', fn(Builder $query) => $query->where('id', '>', 1)->orWhere('name', '!=', 'super_admin')->orderBy('name', 'asc'))
                            ->required()
                            ->inlineLabel()
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])->columns(2),
            ]);
    }

    protected static function generateUsername($set, $get)
    {
        $firstName = $get('first_name');

        if ($get('username') && $firstName) {
            $set('username', $get('username'));
        } else {
            if ($firstName) {
                // Menghapus spasi dari first_name
                $baseUsername = strtolower(Str::slug(str_replace(' ', '', $firstName)));
                $username = $baseUsername;
                $count = 1;

                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $count;
                    $count++;
                }

                $set('username', $username);
            } else {
                $set('username', null);
            }
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jk')
                    ->label('Jenis Kelamin')
                    ->sortable()
                    ->badge()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('status')
                    ->label('Status Akun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role User')
                    ->sortable()
                    ->badge()
                    ->searchable(),
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('Reset Password')
                    ->requiresConfirmation()
                    ->action(
                        function (User $record, array $data): void {
                            $record->update([
                                'password' => Hash::make('password'),
                            ]);

                            Notification::make()
                                ->title('User ' . $record->first_name . ' berhasil reset password')
                                ->success()
                                ->send();
                        }
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name',];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Product $record */

        return [
            $record->first_name . ' ' . $record->last_name,
            $record->jk,

        ];
    }

    public static function getWidgets(): array
    {
        return [
            UsersOverview::class,
        ];
    }
}
