<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningUnitResource\Pages;
use App\Models\LearningUnit;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class LearningUnitResource extends Resource
{
    protected static ?string $model = LearningUnit::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Learning Units';

    protected static ?string $modelLabel = 'Learning Unit';

    protected static ?string $pluralModelLabel = 'Learning Units';

    protected static UnitEnum|string|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isTechAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('roadmap_id')
                    ->relationship('roadmap', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Select::make('unit_type')
                    ->options([
                        'lesson' => 'Lesson',
                        'quiz' => 'Quiz',
                        'challenge' => 'Challenge',
                    ])
                    ->required()
                    ->default('lesson'),

                Forms\Components\TextInput::make('position')
                    ->numeric()
                    ->required()
                    ->default(1),

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

                Tables\Columns\TextColumn::make('roadmap.title')
                    ->label('Roadmap')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('unit_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lesson' => 'primary',
                        'quiz' => 'success',
                        'challenge' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('lessons_count')
                    ->label('Lessons')
                    ->counts('lessons')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roadmap_id')
                    ->relationship('roadmap', 'title')
                    ->label('Roadmap'),
                Tables\Filters\SelectFilter::make('unit_type')
                    ->options([
                        'lesson' => 'Lesson',
                        'quiz' => 'Quiz',
                        'challenge' => 'Challenge',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('toggleActive')
                    ->label(fn (LearningUnit $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (LearningUnit $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (LearningUnit $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (LearningUnit $record) {
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
            ->defaultSort('position', 'asc');
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
            'index' => Pages\ListLearningUnits::route('/'),
            'create' => Pages\CreateLearningUnit::route('/create'),
            'edit' => Pages\EditLearningUnit::route('/{record}/edit'),
        ];
    }
}

