<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 1;

    /* ------------------------------------------------------------------ */
    /*  Form                                                               */
    /* ------------------------------------------------------------------ */

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Product Details')
                ->schema([

                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->localized_name)
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label('Name (English)')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('name_ar')
                        ->label('Name (Arabic)')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Description (English)')
                        ->rows(4)
                        ->maxLength(2000),

                    Forms\Components\Textarea::make('description_ar')
                        ->label('Description (Arabic)')
                        ->rows(4)
                        ->maxLength(2000),

                    Forms\Components\TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('EGP')
                        ->minValue(0.01)
                        ->step(0.01),

                    Forms\Components\TextInput::make('stock')
                        ->required()
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->default(0),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                ])
                ->columns(2),

            /* ------------------------------------------------------------------ */
            /*  Images                                                            */
            /* ------------------------------------------------------------------ */

            Forms\Components\Section::make('Product Images')
                ->schema([

                    Forms\Components\Repeater::make('images')
                        ->relationship()
                        ->schema([

                            Forms\Components\FileUpload::make('path')
                                ->label('Image')
                                ->image()
                                ->disk('public')
                                ->directory('products')
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('1:1')
                                ->imageResizeTargetWidth('800')
                                ->imageResizeTargetHeight('800')
                                ->maxSize(2048)
                                ->required(),

                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->integer()
                                ->default(0),

                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add Image')
                        ->reorderable()
                        ->collapsible(),

                ]),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Table                                                              */
    /* ------------------------------------------------------------------ */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('images.path')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->stacked()
                    ->limit(1),

                Tables\Columns\TextColumn::make('localized_name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.localized_name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 5  => 'warning',
                        default      => 'success',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('id', 'desc')

            ->filters([

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),

                Tables\Filters\Filter::make('out_of_stock')
                    ->query(fn ($query) => $query->where('stock', 0))
                    ->label('Out of Stock'),

            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Relations                                                          */
    /* ------------------------------------------------------------------ */

    public static function getRelations(): array
    {
        return [];
    }

    /* ------------------------------------------------------------------ */
    /*  Pages                                                              */
    /* ------------------------------------------------------------------ */

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}



