<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\City;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informacion personal')
                    ->columns(3)
                    ->schema([  // ← Solo usa schema, NO components
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->hiddenOn('edit')
                            ->required(),
                    ]),
                Section::make('Direccion')
                    ->columns(2)
                    ->schema([
                        Select::make('province_id')
                            ->relationship(name: 'province', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('city_id', null))
                            ->required(),

                        Select::make('city_id')
                            ->options(fn(Get $get): Collection => City::query()
                                ->where('province_id', $get('province_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        TextInput::make('address')
                            ->required(),
                        TextInput::make('postal_code')
                            ->required(),
                    ]),

            ]);
    }  // ← Falta este cierre
}
