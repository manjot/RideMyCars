<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisputeResource\Pages;
use App\Models\Dispute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DisputeResource extends Resource
{
    protected static ?string $model = Dispute::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Legal & Compliance';
    protected static ?string $navigationLabel = 'Disputes & Claims';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dispute Details')
                    ->schema([
                        Forms\Components\TextInput::make('dispute_code')
                            ->label('Dispute Ref')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('service_type')
                            ->options([
                                'ride' => 'On-Demand Ride',
                                'rental' => 'Vehicle Rental',
                                'chauffeur' => 'Chauffeur / Driver Hiring',
                                'delivery' => 'Package Delivery',
                                'other' => 'Other / Billing',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('booking_reference')
                            ->label('Booking Ref Code'),
                        Forms\Components\TextInput::make('category')
                            ->required(),
                        Forms\Components\TextInput::make('contact_email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('contact_phone'),
                        Forms\Components\Toggle::make('is_within_72h')
                            ->label('Submitted Within 72-Hour Contractual Window'),
                        Forms\Components\DateTimePicker::make('event_completed_at')
                            ->label('Event Completed Date'),
                        Forms\Components\DateTimePicker::make('deadline_at')
                            ->label('72h Window Deadline'),
                    ])->columns(2),

                Forms\Components\Section::make('Statement & Evidence')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('evidence_photo_url')
                            ->label('Supporting Evidence Photo')
                            ->image()
                            ->directory('disputes/evidence')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Administrative Action & Resolution')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'submitted' => 'Submitted',
                                'under_review' => 'Under Review',
                                'awaiting_info' => 'Awaiting Information',
                                'resolved' => 'Resolved',
                                'rejected' => 'Rejected',
                                'closed' => 'Closed',
                            ])
                            ->required()
                            ->default('submitted'),
                        Forms\Components\DateTimePicker::make('resolved_at'),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Administrative Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dispute_code')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ride' => 'info',
                        'rental' => 'success',
                        'chauffeur' => 'warning',
                        'delivery' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('booking_reference')
                    ->label('Ref')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_within_72h')
                    ->label('Within 72h')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'under_review' => 'primary',
                        'awaiting_info' => 'warning',
                        'resolved' => 'success',
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
                        'awaiting_info' => 'Awaiting Information',
                        'resolved' => 'Resolved',
                        'rejected' => 'Rejected',
                        'closed' => 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('service_type')
                    ->options([
                        'ride' => 'Ride Hailing',
                        'rental' => 'Vehicle Rental',
                        'chauffeur' => 'Chauffeur / Driver',
                        'delivery' => 'Package Delivery',
                        'other' => 'Other',
                    ]),
                Tables\Filters\TernaryFilter::make('is_within_72h')
                    ->label('Within 72h Window'),
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
            'index' => Pages\ListDisputes::route('/'),
            'create' => Pages\CreateDispute::route('/create'),
            'edit' => Pages\EditDispute::route('/{record}/edit'),
        ];
    }
}
