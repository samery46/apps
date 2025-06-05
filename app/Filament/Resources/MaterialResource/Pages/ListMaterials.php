<?php

namespace App\Filament\Resources\MaterialResource\Pages;

use App\Filament\Resources\MaterialResource;
use App\Imports\MaterialImport;
use App\Models\Material;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;

class ListMaterials extends ListRecords
{
    protected static string $resource = MaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Material'),
            Action::make('importMaterials')
                ->label('Import')
                ->icon('heroicon-s-arrow-down-tray')
                ->color('info')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Template Material')
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);

                    try {
                        Excel::import(new MaterialImport, $file);
                        Notification::make()
                            ->title('Materials Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Materials Failed to Import')
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('Template')
                ->url(route('import-material'))
                ->color('warning'),

        ];
    }

    public function getTableQuery(): Builder
    {
        $query = Material::query()->where('is_aktif', true); // Hanya data aktif

        if (auth()->check() && auth()->user()->id === 1) {
            // Admin melihat semua data, tanpa filter tambahan
        } else {
            // Filter berdasarkan plant_id yang dimiliki user dari tabel pivot `material_plant`
            $userPlantIds = auth()->user()->plants->pluck('id')->toArray();

            $query->whereHas('plants', function ($q) use ($userPlantIds) {
                $q->whereIn('plant_id', $userPlantIds);
            });
        }

        return $query;
    }
}
