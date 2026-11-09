<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditAdminProfile extends BaseEditProfile
{
    public static function getLabel(): string
    {
        return 'Edit Profile';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Admin Profile Details')
                    ->description('Update your profile photo, name, email address, phone number, and location details.')
                    ->schema([
                        FileUpload::make('avatar')
                            ->label('Profile Image')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('avatars')
                            ->maxSize(5120)
                            ->alignCenter()
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                $this->getNameFormComponent()
                                    ->label('Full Name')
                                    ->placeholder('Enter your full name'),

                                $this->getEmailFormComponent()
                                    ->label('Email Address')
                                    ->placeholder('admin@ridemycars.com'),

                                TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->placeholder('+1 800 555 0199 / +233 55 977 6761')
                                    ->maxLength(50),

                                TextInput::make('country')
                                    ->label('Country')
                                    ->placeholder('e.g. USA, Ghana, United Kingdom')
                                    ->maxLength(100),

                                TextInput::make('city')
                                    ->label('City')
                                    ->placeholder('e.g. Accra, New York')
                                    ->maxLength(100),

                                TextInput::make('role')
                                    ->label('Account Role')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn (?string $state): string => strtoupper($state ?: 'ADMIN')),
                            ]),
                    ]),

                Section::make('Change Password')
                    ->description('Leave these fields blank if you do not want to change your current password.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                $this->getPasswordFormComponent()
                                    ->label('New Password')
                                    ->placeholder('Enter new password'),

                                $this->getPasswordConfirmationFormComponent()
                                    ->label('Confirm New Password')
                                    ->placeholder('Re-enter new password')
                                    ->visible(true)
                                    ->required(false)
                                    ->requiredWith('password'),
                            ]),
                    ]),
            ]);
    }
}

