<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoadmapResource\Pages;
use App\Models\Roadmap;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class RoadmapResource extends Resource
{
    protected static ?string $model = Roadmap::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Roadmaps';

    protected static ?string $modelLabel = 'Roadmap';

    protected static ?string $pluralModelLabel = 'Roadmaps';

    protected static UnitEnum|string|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isTechAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('description')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\Select::make('level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ])
                    ->required()
                    ->default('beginner'),

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

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'beginner' => 'success',
                        'intermediate' => 'warning',
                        'advanced' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('learning_units_count')
                    ->label('Units')
                    ->counts('learningUnits')
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label('Enrollments')
                    ->counts('enrollments')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('toggleActive')
                    ->label(fn (Roadmap $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Roadmap $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Roadmap $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Roadmap $record) {
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
            'index' => Pages\ListRoadmaps::route('/'),
            'create' => Pages\CreateRoadmap::route('/create'),
            'edit' => Pages\EditRoadmap::route('/{record}/edit'),
        ];
    }
}

