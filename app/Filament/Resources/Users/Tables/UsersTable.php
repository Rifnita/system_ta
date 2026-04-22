<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Password;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo_url')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.svg'))
                    ->size(40),
                
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon(fn ($record): string => filled($record->email_verified_at) 
                        ? 'heroicon-s-check-badge' 
                        : 'heroicon-o-x-circle')
                    ->iconColor(fn ($record): string => filled($record->email_verified_at) 
                        ? 'success' 
                        : 'gray'),
                
                TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Username berhasil disalin!')
                    ->icon('heroicon-o-at-symbol'),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'user' => 'info',
                        default => 'gray',
                    })
                    ->searchable(),
                
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(30)
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-map-pin'),
                
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'info' : 'danger')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->placeholder('Semua')
                    ->trueLabel('Terverifikasi')
                    ->falseLabel('Belum Terverifikasi')
                    ->nullable()
                    ->native(false),
                
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Nonaktif',
                    ]),
                
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Ubah')
                        ->icon('heroicon-o-pencil-square'),
                    
                    Action::make('resendVerification')
                        ->label('Kirim Email Verifikasi')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->visible(fn ($record): bool => is_null($record->email_verified_at))
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Ulang Email Verifikasi')
                        ->modalDescription('Email verifikasi akan dikirim ke alamat email pengguna.')
                        ->modalSubmitActionLabel('Kirim Email')
                        ->modalCancelActionLabel('Batal')
                        ->action(function ($record) {
                            $record->sendEmailVerificationNotification();
                            return true;
                        })
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Email Verifikasi Terkirim')
                                ->body('Email verifikasi telah dikirim ke pengguna.')
                        ),
                    
                    Action::make('sendResetPassword')
                        ->label('Kirim Reset Kata Sandi')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Tautan Reset Kata Sandi')
                        ->modalDescription('Tautan reset kata sandi akan dikirim ke alamat email pengguna.')
                        ->modalSubmitActionLabel('Kirim Email')
                        ->modalCancelActionLabel('Batal')
                        ->action(function ($record) {
                            Password::sendResetLink(['email' => $record->email]);
                            return true;
                        })
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Email Reset Kata Sandi Terkirim')
                                ->body('Tautan reset kata sandi telah dikirim ke pengguna.')
                        ),
                    
                    DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Pengguna')
                        ->modalDescription('Yakin ingin menghapus pengguna ini? Data yang dihapus tidak dapat dipulihkan.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('sendVerificationEmails')
                        ->label('Kirim Email Verifikasi')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Email Verifikasi')
                        ->modalDescription('Email verifikasi akan dikirim ke semua pengguna terpilih yang belum terverifikasi.')
                        ->modalSubmitActionLabel('Kirim Email')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (is_null($record->email_verified_at)) {
                                    $record->sendEmailVerificationNotification();
                                    $count++;
                                }
                            }
                            return $count;
                        })
                        ->successNotification(
                            fn ($result) => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Email Verifikasi Terkirim')
                                ->body("Email verifikasi telah dikirim ke {$result} pengguna.")
                        )
                        ->deselectRecordsAfterCompletion(),
                    
                    BulkAction::make('sendResetPasswordEmails')
                        ->label('Kirim Reset Kata Sandi')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Tautan Reset Kata Sandi')
                        ->modalDescription('Tautan reset kata sandi akan dikirim ke semua pengguna terpilih.')
                        ->modalSubmitActionLabel('Kirim Email')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                Password::sendResetLink(['email' => $record->email]);
                                $count++;
                            }
                            return $count;
                        })
                        ->successNotification(
                            fn ($result) => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Email Reset Kata Sandi Terkirim')
                                ->body("Tautan reset kata sandi telah dikirim ke {$result} pengguna.")
                        )
                        ->deselectRecordsAfterCompletion(),
                    
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
