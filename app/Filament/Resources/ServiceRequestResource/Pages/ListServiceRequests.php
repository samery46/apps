<?php

namespace App\Filament\Resources\ServiceRequestResource\Pages;

use App\Filament\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListServiceRequests extends ListRecords
{
    protected static string $resource = ServiceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Service Request'),
        ];
    }


        public function getTableQuery(): Builder
        {
            $query = ServiceRequest::query(); // Query utama dari ServiceRequest

            if (auth()->check() && auth()->user()->id === 1) {
                // Admin melihat semua data, tanpa filter tambahan
            } else {
                // Ambil plant_id yang dimiliki user
                $userPlantIds = auth()->user()->plants->pluck('id')->toArray();

                // Pastikan hanya ServiceRequest dengan asset yang terkait plant_id user yang tampil
                $query->whereHas('asset', function ($q) use ($userPlantIds) {
                    $q->whereIn('plant_id', $userPlantIds);
                });
            }

            return $query;
        }
}
