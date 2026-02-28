<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChallengeResource\Pages;
use App\Models\Challenge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationLabel = 'Challenges';

    protected static ?string $modelLabel = 'Challenge';

    protected static ?string $pluralModelLabel = 'Challenges';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 8;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isTechAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('learning_unit_id')
                    ->relationship('learningUnit', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('description')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('min_xp')
                    ->label('Min XP')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\Select::make('language')
                    ->options([
                        'javascript' => 'JavaScript',
                        'python' => 'Python',
                        'java' => 'Java',
                        'cpp' => 'C++',
                        'c' => 'C',
                    ])
                    ->required()
                    ->default('javascript'),

                Forms\Components\Textarea::make('starter_code')
                    ->label('Starter Code')
                    ->rows(6)
                    ->columnSpanFull()
                    ->required(),

                Forms\Components\Repeater::make('test_cases')
                    ->schema([
                        Forms\Components\TextInput::make('input')
                            ->label('Input')
                            ->required(),
                        Forms\Components\TextInput::make('expected_output')
                            ->label('Expected Output')
                            ->required(),
                    ])
                    ->defaultItems(1)
                    ->minItems(1)
                    ->columnSpanFull()
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('learningUnit.title')
                    ->label('Learning Unit')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('min_xp')
                    ->label('Min XP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('language')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'javascript' => 'primary',
                        'python' => 'success',
                        'java' => 'warning',
                        'cpp' => 'info',
                        'c' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('learning_unit_id')
                    ->relationship('learningUnit', 'title')
                    ->label('Learning Unit'),
                Tables\Filters\SelectFilter::make('language')
                    ->options([
                        'javascript' => 'JavaScript',
                        'python' => 'Python',
                        'java' => 'Java',
                        'cpp' => 'C++',
                        'c' => 'C',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('toggleActive')
                    ->label(fn (Challenge $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Challenge $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Challenge $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Challenge $record) {
                        $record->update(['is_active' => !$record->is_active]);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListChallenges::route('/'),
            'create' => Pages\CreateChallenge::route('/create'),
            'edit' => Pages\EditChallenge::route('/{record}/edit'),
        ];
    }
}

