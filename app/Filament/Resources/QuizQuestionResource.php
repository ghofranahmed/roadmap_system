<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizQuestionResource\Pages;
use App\Models\QuizQuestion;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class QuizQuestionResource extends Resource
{
    protected static ?string $model = QuizQuestion::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Quiz Questions';

    protected static ?string $modelLabel = 'Quiz Question';

    protected static ?string $pluralModelLabel = 'Quiz Questions';

    protected static UnitEnum|string|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 7;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isTechAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('quiz_id')
                    ->relationship('quiz', 'id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "Quiz #{$record->id} - {$record->learningUnit->title}"),

                Forms\Components\Textarea::make('question_text')
                    ->label('Question')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('options')
                    ->schema([
                        Forms\Components\TextInput::make('option')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->defaultItems(4)
                    ->minItems(2)
                    ->maxItems(6)
                    ->columnSpanFull()
                    ->required(),

                Forms\Components\TextInput::make('correct_answer')
                    ->label('Correct Answer')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Enter the exact option text that is correct'),

                Forms\Components\TextInput::make('question_xp')
                    ->label('Question XP')
                    ->numeric()
                    ->default(10)
                    ->required(),

                Forms\Components\TextInput::make('order')
                    ->label('Order')
                    ->numeric()
                    ->default(1)
                    ->required(),
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

                Tables\Columns\TextColumn::make('quiz.id')
                    ->label('Quiz ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('question_text')
                    ->label('Question')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('correct_answer')
                    ->label('Correct Answer')
                    ->limit(30),

                Tables\Columns\TextColumn::make('question_xp')
                    ->label('XP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('quiz_id')
                    ->relationship('quiz', 'id')
                    ->label('Quiz'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
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
            'index' => Pages\ListQuizQuestions::route('/'),
            'create' => Pages\CreateQuizQuestion::route('/create'),
            'edit' => Pages\EditQuizQuestion::route('/{record}/edit'),
        ];
    }
}

