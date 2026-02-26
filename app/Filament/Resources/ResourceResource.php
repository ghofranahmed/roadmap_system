<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResourceResource\Pages;
use App\Models\Resource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource as FilamentResource;
use Filament\Tables;
use Filament\Tables\Table;

class ResourceResource extends FilamentResource
{
    protected static ?string $model = Resource::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Resources';

    protected static ?string $modelLabel = 'Resource';

    protected static ?string $pluralModelLabel = 'Resources';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isTechAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sub_lesson_id')
                    ->relationship('subLesson', 'description')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Select::make('type')
                    ->options([
                        'video' => 'Video',
                        'article' => 'Article',
                        'document' => 'Document',
                        'tutorial' => 'Tutorial',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->default('article'),

                Forms\Components\Select::make('language')
                    ->options([
                        'ar' => 'Arabic',
                        'en' => 'English',
                        'fr' => 'French',
                        'es' => 'Spanish',
                    ])
                    ->default('en'),

                Forms\Components\TextInput::make('link')
                    ->url()
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('subLesson.description')
                    ->label('Sub-Lesson')
                    ->limit(30)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'video' => 'primary',
                        'article' => 'success',
                        'document' => 'warning',
                        'tutorial' => 'info',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('language')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('link')
                    ->limit(30)
                    ->copyable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'video' => 'Video',
                        'article' => 'Article',
                        'document' => 'Document',
                        'tutorial' => 'Tutorial',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('language')
                    ->options([
                        'ar' => 'Arabic',
                        'en' => 'English',
                        'fr' => 'French',
                        'es' => 'Spanish',
                    ]),
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
            'index' => Pages\ListResources::route('/'),
            'create' => Pages\CreateResource::route('/create'),
            'edit' => Pages\EditResource::route('/{record}/edit'),
        ];
    }
}

