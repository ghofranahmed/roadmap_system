<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubLessonResource\Pages;
use App\Models\SubLesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubLessonResource extends Resource
{
    protected static ?string $model = SubLesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Sub-Lessons';

    protected static ?string $modelLabel = 'Sub-Lesson';

    protected static ?string $pluralModelLabel = 'Sub-Lessons';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isTechAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('lesson_id')
                    ->relationship('lesson', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Textarea::make('description')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('position')
                    ->numeric()
                    ->required()
                    ->default(1),
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

                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('position')
                    ->sortable(),

                Tables\Columns\TextColumn::make('resources_count')
                    ->label('Resources')
                    ->counts('resources')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lesson_id')
                    ->relationship('lesson', 'title')
                    ->label('Lesson'),
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
            'index' => Pages\ListSubLessons::route('/'),
            'create' => Pages\CreateSubLesson::route('/create'),
            'edit' => Pages\EditSubLesson::route('/{record}/edit'),
        ];
    }
}

