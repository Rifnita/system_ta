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
                ImageColumn::make('profile_photo_path')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.svg'))
                    ->size(40),
                
                TextColumn::make('name')
                    ->label('Name')
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
                    ->copyMessage('Username copied!')
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
                    ->label('Address')
                    ->limit(30)
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-map-pin'),
                
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'info' : 'danger')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->placeholder('All')
                    ->trueLabel('Verified')
                    ->falseLabel('Unverified')
                    ->nullable()
                    ->native(false),
                
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
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
                        ->label('Edit')
                        ->icon('heroicon-o-pencil-square'),
                    
                    Action::make('resendVerification')
                        ->label('Send Verification Email')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->visible(fn ($record): bool => is_null($record->email_verified_at))
                        ->requiresConfirmation()
                        ->modalHeading('Resend Verification Email')
                        ->modalDescription('Verification email will be sent to the user email address.')
                        ->modalSubmitActionLabel('Send Email')
                        ->modalCancelActionLabel('Cancel')
                        ->action(function ($record) {
                            $record->sendEmailVerificationNotification();
                            return true;
                        })
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Verification Email Sent')
                                ->body('Verification email has been sent to the user.')
                        ),
                    
                    Action::make('sendResetPassword')
                        ->label('Send Password Reset')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Send Password Reset Link')
                        ->modalDescription('Password reset link will be sent to the user email address.')
                        ->modalSubmitActionLabel('Send Email')
                        ->modalCancelActionLabel('Cancel')
                        ->action(function ($record) {
                            Password::sendResetLink(['email' => $record->email]);
                            return true;
                        })
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Password Reset Email Sent')
                                ->body('Password reset link has been sent to the user.')
                        ),
                    
                    DeleteAction::make()
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Delete User')
                        ->modalDescription('Are you sure you want to delete this user? Deleted data cannot be restored.')
                        ->modalSubmitActionLabel('Yes, Delete')
                        ->modalCancelActionLabel('Cancel'),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('sendVerificationEmails')
                        ->label('Send Verification Emails')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Send Verification Emails')
                        ->modalDescription('Verification emails will be sent to all selected unverified users.')
                        ->modalSubmitActionLabel('Send Email')
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
                                ->title('Verification Emails Sent')
                                ->body("Verification emails have been sent to {$result} users.")
                        )
                        ->deselectRecordsAfterCompletion(),
                    
                    BulkAction::make('sendResetPasswordEmails')
                        ->label('Send Password Reset')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Send Password Reset Links')
                        ->modalDescription('Password reset links will be sent to all selected users.')
                        ->modalSubmitActionLabel('Send Email')
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
                                ->title('Password Reset Emails Sent')
                                ->body("Password reset links have been sent to {$result} users.")
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
