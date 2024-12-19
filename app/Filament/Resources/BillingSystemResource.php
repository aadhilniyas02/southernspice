<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingSystemResource\Pages;
use App\Models\BillingSystem;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\DateColumn;
use Filament\Tables\Columns\DateTimeColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Button;
use Filament\Forms\Components\Placeholder; 
use Filament\Forms\Actions\SaveAction;



class BillingSystemResource extends Resource
{
    protected static ?string $model = BillingSystem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            // Bill Date and Time Section
            Forms\Components\Section::make('Billing Information')->schema([
                Forms\Components\DatePicker::make('bill_date')
                    ->label('Bill Date')
                    ->default(now()->toDateString()) // Set the current date as default
                    ->required(),
                Forms\Components\TimePicker::make('bill_time')
                    ->label('Bill Time')
                    ->default(now()->format('H:i')) // Set the current time as default
                    ->required(),
            ])->columns(2),

            // Billing Items Section
            Forms\Components\Section::make('Billing Items')->schema([
                Repeater::make('billingItems')
                    ->relationship('billingItems')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(
                                Product::query()
                                    ->pluck('product_name', 'id')
                            )
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                // Fetch unit price from the selected product
                                $product = Product::find($state);
                                $set('unit_price', $product ? $product->price : 0);
                            }),
                        TextInput::make('order_quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Update total price when quantity changes
                                $set('total_price', $state * $get('unit_price'));
                            }),
                        TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->disabled(),
                        TextInput::make('total_price')
                            ->label('Total Price')
                            ->disabled()
                            ->reactive(),
                    ])
                    ->createItemButtonLabel('Add Billing Item')
                    ->columns(4)
                    ->afterStateUpdated(function ($state, $set, $record) {
                        // Calculate the total amount dynamically
                        $totalAmount = collect($state)->sum(fn($item) => $item['total_price'] ?? 0);
                        $set('total_amount', $totalAmount);

                        if ($record) { // Only update if the record exists
                            $record->update(['total_amount' => $totalAmount]);
                        }
                    }),

                // Display Total Amount
                Placeholder::make('total_amount')
                    ->label('Total Amount')
                    ->content(function ($get, $set) {
                        $total = 0;
                        $billingItems = $get('billingItems');
                        if ($billingItems) {
                            foreach ($billingItems as $item) {
                                $total += $item['total_price'] ?? 0;
                            }
                        }
                        $set('total_amount', $total);
                        return number_format($total, 2); // Format the total amount
                    }),
            ]),
        ]);
            
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('bill_date')  // Use DateTimeColumn
                ->label('Bill Date')
                ->sortable() 
                ->searchable() // Allow sorting by date
                ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('Y-m-d')),
            TextColumn::make('bill_time')
                ->label('Bill Time')
                ->sortable()  // Allow sorting by time
                ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('H:i')), // Format the time

            TextColumn::make('total_amount')
                ->label('Total Amount')
                ->numeric()
                ->searchable()
                ->sortable()  // Allow sorting by amount
                ->money('LKR') 
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillingSystems::route('/'),
            'create' => Pages\CreateBillingSystem::route('/create'),
            'edit' => Pages\EditBillingSystem::route('/{record}/edit'),
        ];
    }
}
