<?php

namespace App\Filament\Resources\RelationManagers;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;
use Spatie\Comments\Models\Comment;
use Spatie\Comments\Models\Concerns\Interfaces\CanComment;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\MarkdownEditor::make('original_text')
                    ->label('Comment')
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('commentator.name')
                    ->getStateUsing(function (Comment $record): string {
                        /** @var CanComment $commentator */
                        $commentator = $record->commentator;

                        return $commentator?->commentatorProperties()?->name ?? 'Guest';
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(function (Comment $record): string {
                        return $record->isApproved() ? 'Approved' : 'Pending';
                    })
                    ->colors([
                        'success' => 'Approved',
                        'warning' => 'Pending',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('approved_at')
                    ->label('Approval status')
                    ->placeholder('All')
                    ->trueLabel('Approved')
                    ->falseLabel('Pending')
                    ->default(0)
                    ->nullable(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->action(function (Comment $record) {
                        $record->approve();

                        Filament::notify('success', 'Approved');
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-s-check')
                    ->hidden(fn (Comment $record): bool => $record->isApproved()),
                Tables\Actions\ViewAction::make()
                    ->label('Preview')
                    ->modalContent(fn (Comment $record): HtmlString => new HtmlString($record->text))
                    ->form([]),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reject')
                    ->action(function (Comment $record) {
                        $record->reject();

                        Filament::notify('success', 'Rejected');
                    })
                    ->requiresConfirmation()
                    ->successNotificationMessage('Rejected')
                    ->color('danger')
                    ->icon('heroicon-s-x')
                    ->visible(fn (Comment $record): bool => $record->isApproved()),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve')
                    ->action(function (Collection $records) {
                        $records->each->approve();

                        Filament::notify('success', 'Approved');
                    })
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-s-check'),
                Tables\Actions\BulkAction::make('reject')
                    ->action(function (Collection $records) {
                        $records->each->reject();

                        Filament::notify('success', 'Rejected');
                    })
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->successNotificationMessage('Rejected')
                    ->color('danger')
                    ->icon('heroicon-s-x'),
            ]);
    }
}
