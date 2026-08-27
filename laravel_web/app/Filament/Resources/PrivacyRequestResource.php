<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrivacyRequestResource\Pages;
use App\Models\PrivacyRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrivacyRequestResource extends Resource
{
    protected static ?string $model = PrivacyRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Legal & Compliance';
    protected static ?string $navigationLabel = 'Privacy & Data Rights';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Statutory Privacy Request')
                    ->schema([
                        Forms\Components\TextInput::make('request_code')
                            ->label('Request Ref Code')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable(),
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\Select::make('request_type')
                            ->options([
                                'access' => 'Right of Access (Copy of Data)',
                                'erasure' => 'Right to Erasure (Account Deletion)',
                                'portability' => 'Right to Data Portability (Export)',
                                'rectification' => 'Right to Rectification',
                                'restriction' => 'Right to Restrict Processing',
                                'objection' => 'Right to Object to Profiling',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Justification & Details')
                    ->schema([
                        Forms\Components\Textarea::make('details')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('DPO Administrative Action')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'submitted' => 'Submitted',
                                'under_review' => 'Under Review',
                                'completed' => 'Completed',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('submitted'),
                        Forms\Components\DateTimePicker::make('completed_at'),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal DPO Administrative Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_code')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('request_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'erasure' => 'danger',
                        'access' => 'info',
                        'portability' => 'primary',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'under_review' => 'warning',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('request_type')
                    ->options([
                        'access' => 'Right of Access',
                        'erasure' => 'Right to Erasure',
                        'portability' => 'Right to Data Portability',
                        'rectification' => 'Right to Rectification',
                        'restriction' => 'Right to Restrict Processing',
                        'objection' => 'Right to Object',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrivacyRequests::route('/'),
            'create' => Pages\CreatePrivacyRequest::route('/create'),
            'edit' => Pages\EditPrivacyRequest::route('/{record}/edit'),
        ];
    }
}
