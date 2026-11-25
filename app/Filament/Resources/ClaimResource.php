<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClaimResource\Pages;
use App\Filament\Resources\ClaimResource\RelationManagers;
use App\Exports\ClaimExport;
use App\Models\Claim;
use App\Models\Plant;
use App\Models\Asset;
use App\Models\WorkflowConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ClaimNotificationService;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class ClaimResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Claim::class;

    protected static ?string $pluralModelLabel = 'Klaim Asuransi';

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'Asset Management';

    protected static ?int $navigationSort = 315;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Plant')
                ->description('Detail Plant')
                ->schema([

                Forms\Components\TextInput::make('ticket_number')
                    ->label('Nomor Tiket')
                    ->default(fn () => Claim::generateTicketNumber(null))
                    ->readOnly()
                    ->columnSpan(3)
                    ->required(),

                Forms\Components\Select::make('plant_id')
                    ->label('Plant')
                    ->placeholder('Cari kode atau nama Plant')
                    ->searchable()
                    ->columnSpan(3)
                    ->preload()
                    ->live()
                    ->disabled(fn ($record) => $record && ! $record->canBeEdited())
                    ->getSearchResultsUsing(function (string $search) {
                        return Plant::where(function ($query) use ($search) {
                            $query->where('nama', 'like', "%{$search}%")
                                ->orWhere('kode', 'like', "%{$search}%");
                        })
                            ->get(['kode', 'nama', 'id'])
                            ->mapWithKeys(function ($plant) {
                                return [$plant->id => $plant->kode . ' - ' . $plant->nama];
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $plant = Plant::find($value);
                        return $plant ? $plant->kode . ' - ' . $plant->nama : null;
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('asset_id', null);
                        $set('ticket_number', Claim::generateTicketNumber($state));
                    })
                    ->rules([
                        fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                            if ($value) {
                                $hasWorkflow = WorkflowConfig::where('plant_id', $value)->exists();
                                if (! $hasWorkflow) {
                                    $fail('Plant yang dipilih belum memiliki konfigurasi alur approval! Silakan atur terlebih dahulu di menu Setting Workflow Config.');
                                }
                            }
                        },
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('tgl_kejadian')
                    ->label('Tanggal Kejadian')
                    ->columnSpan(2)
                    ->disabled(fn ($record) => $record && ! $record->canBeEdited())
                    ->required(),

                ])->columns(8),



                Forms\Components\Hidden::make('created_by_user_id')
                    ->default(auth()->id()),

                Forms\Components\Section::make('Asset')
                ->description('Detail Asset')
                ->schema([

                Forms\Components\Select::make('asset_id')
                    ->label('Asset')
                    ->placeholder('Cari nomor atau nama Asset')
                    ->searchable()
                    ->columnSpan(6)
                    ->preload()
                    ->disabled(fn ($record) => $record && ! $record->canBeEdited())
                    ->getSearchResultsUsing(function (string $search) {
                        return Asset::where(function ($query) use ($search) {
                            $query->where('nomor', 'like', "%{$search}%")
                                ->orWhere('nama', 'like', "%{$search}%");
                        })
                            ->get(['nomor', 'nama', 'id'])
                            ->mapWithKeys(function ($asset) {
                                return [$asset->id => $asset->nomor . ' - ' . $asset->nama];
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $asset = Asset::find($value);
                        return $asset ? $asset->nomor . ' - ' . $asset->nama : null;
                    })
                    ->required(),    
                    
                Forms\Components\Select::make('incident_type')
                    ->label('Jenis Insiden')
                    ->columnSpan(3)
                    ->options([
                        'rusak' => 'Rusak / Kerusakan Fisik',
                        'hilang' => 'Hilang / Pencurian',
                    ])
                    ->disabled(fn ($record) => $record && ! $record->canBeEdited())
                    ->required(),               

                Forms\Components\TextInput::make('estimasi_kerugian')
                    ->label('Estimasi Kerugian')
                    ->numeric()
                    ->columnSpan(3)
                    ->prefix('Rp')
                    ->formatStateUsing(fn ($state) => filled($state) ? (int) $state : null)
                    ->disabled(fn ($record) => $record && ! $record->canBeEdited())
                    ->required(),                    


                ])->columns(12),




                

                Forms\Components\Textarea::make('latar_belakang')
                    ->required()
                    ->disabled(fn ($record) => $record && ! $record->canBeEdited())
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('kronologi')
                    ->required()
                    ->disabled(fn ($record) => $record && ! $record->canBeEdited())
                    ->columnSpanFull(),

                // Repeater untuk Multiple Attachments
                Forms\Components\Repeater::make('attachments')
                    ->relationship('attachments')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Berkas Bukti')
                            ->directory('claim-attachments')
                            ->disk('public')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->openable()
                            ->downloadable()
                            ->previewable(true)
                            ->storeFileNamesIn('nama_file_asli')
                            ->deletable(false) // Mencegah hapus file yang sudah tersimpan
                            ->required(),

                        Forms\Components\TextInput::make('keterangan')
                            ->label('Keterangan File')
                            ->placeholder('Contoh: Foto fisik aset / berkas kronologi'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->addable(fn ($record) => $record === null || $record->status !== 'rejected')
                    ->reorderable(false)
                    ->deleteAction(
                        fn (Forms\Components\Actions\Action $action) => $action->visible(
                            // Hanya berkas baru yang belum tersimpan di database yang bisa dicancel
                            fn (array $arguments) => ! str_starts_with($arguments['item'] ?? '', 'record-')
                        )
                    ),

                // Pratinjau Alur Approval Dinamis (Khusus Form Pembuatan Baru)
                Forms\Components\Section::make('Pratinjau Alur Approval')
                    ->description('User Approval sesuai konfigurasi workflow')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Forms\Components\Placeholder::make('workflow_preview')
                            ->label('')
                            ->content(function (Get $get) {
                                $plantId = $get('plant_id');

                                if (! $plantId) {
                                    return new HtmlString('
                                        <div class="text-sm text-gray-500 italic p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
                                            ℹ️ Silakan pilih <strong>Plant</strong> terlebih dahulu untuk melihat user approver.
                                        </div>
                                    ');
                                }

                                $workflows = WorkflowConfig::where('plant_id', $plantId)
                                    ->with('user')
                                    ->orderBy('step_order', 'asc')
                                    ->get();

                                if ($workflows->isEmpty()) {
                                    return new HtmlString('
                                        <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-lg text-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <span class="text-lg">⚠️</span>
                                                <span><strong>Perhatian:</strong> Belum ada konfigurasi Workflow approval! .</span>
                                            </div>
                                            <a href="/admin/workflow-configs" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-red-600 hover:bg-red-700 text-white rounded-md transition shadow-sm whitespace-nowrap">
                                                Atur Workflow &rarr;
                                            </a>
                                        </div>
                                    ');
                                }

                                $html = '<div class="space-y-2">';
                                $html .= '<p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Urutan User approval secara bertahap:</p>';
                                $html .= '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">';
                                foreach ($workflows as $wf) {
                                    $userName = e($wf->user->name ?? 'User Tidak Ditemukan');
                                    $userEmail = e($wf->user->email ?? '-');
                                    $step = $wf->step_order;

                                    $html .= "
                                        <div class=\"flex items-center gap-3 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:border-primary-400 transition\">
                                            <div class=\"flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 font-bold flex items-center justify-center text-xs ring-2 ring-primary-500/20\">
                                                #{$step}
                                            </div>
                                            <div class=\"min-w-0 flex-1\">
                                                <p class=\"text-xs font-semibold text-gray-800 dark:text-gray-100 truncate\">{$userName}</p>
                                                <p class=\"text-[11px] text-gray-500 dark:text-gray-400 truncate\">{$userEmail}</p>
                                            </div>
                                        </div>
                                    ";
                                }
                                $html .= '</div></div>';

                                return new HtmlString($html);
                            }),
                    ])
                    ->collapsible()
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record === null || ! $record->exists),



                    // Riwayat Approval Bertingkat
                Forms\Components\Section::make('Riwayat Approval')
                    ->description('Tahapan dan status User Approval')
                    ->schema([
                        self::getApprovalHistoryRepeater(),
                    ])
                    ->collapsed(false)
                    ->collapsible()
                    ->visible(fn ($record) => $record !== null && $record->exists),
            ]);
    }

    public static function getApprovalHistoryRepeater(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('approvals')
            ->label('List Approval')
            ->relationship('approvals')
            ->schema([
                Forms\Components\TextInput::make('step_order')
                    ->label('Tahap')
                    ->columnSpan(1)
                    ->disabled(),

                Forms\Components\Select::make('user_id')
                    ->label('Approver')
                    ->options(fn () => \App\Models\User::pluck('name', 'id')->map(fn ($n) => (string) ($n ?? 'User'))->toArray())
                    ->columnSpan(2)
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->columnSpan(2)
                    ->disabled(),

                Forms\Components\DateTimePicker::make('action_at')
                    ->label('Waktu Diproses')
                    ->columnSpan(2)
                    ->disabled(),

                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan')
                    ->columnSpan(3)
                    ->disabled(),
            ])
            ->columns(10)
            ->defaultItems(0)
            ->addable(false)
            ->deletable(false)
            ->reorderable(false)
            ->dehydrated(false)
            ->saveRelationshipsUsing(null);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('No Tiket')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plant.kode')
                    ->label('Plant')
                    ->getStateUsing(function ($record) {
                        return $record->plant ? ($record->plant->kode . ' - ' . $record->plant->nama) : '-';
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('asset.nama')
                    ->label('Asset')
                    ->searchable(),

                Tables\Columns\TextColumn::make('incident_type')
                    ->label('Jenis')
                    ->badge(),

                Tables\Columns\TextColumn::make('estimasi_kerugian')
                    ->label('Estimasi Kerugian')
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 0, ',', '.') : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('attachments_preview')
                    ->label('Lampiran Bukti')
                    ->getStateUsing(fn ($record) => $record->attachments)
                    ->formatStateUsing(function ($state, $record) {
                        $attachments = $record->attachments;
                        if (! $attachments || $attachments->isEmpty()) {
                            return '<span class="text-xs text-gray-400 italic">Tidak ada berkas</span>';
                        }

                        $html = '<div class="flex items-center gap-1.5 flex-wrap">';
                        foreach ($attachments as $att) {
                            if (empty($att->file_path)) {
                                continue;
                            }

                            $url = \Illuminate\Support\Facades\Storage::disk('public')->url($att->file_path);
                            $nama = e($att->nama_file_asli ?: basename($att->file_path));
                            $ext = strtolower(pathinfo($att->file_path, PATHINFO_EXTENSION));

                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                                $html .= "<a href=\"{$url}\" target=\"_blank\" title=\"{$nama}\" class=\"inline-block transition transform hover:scale-110\">
                                    <img src=\"{$url}\" alt=\"{$nama}\" class=\"w-9 h-9 object-cover rounded border border-gray-300 dark:border-gray-600 shadow-sm hover:ring-2 hover:ring-primary-500\">
                                </a>";
                            } elseif ($ext === 'pdf') {
                                $html .= "<a href=\"{$url}\" target=\"_blank\" title=\"{$nama}\" class=\"inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-900/50 dark:text-red-300 rounded border border-red-200 transition shadow-sm\">
                                    <span>📄</span> PDF
                                </a>";
                            } else {
                                $badgeText = strtoupper($ext ?: 'FILE');
                                $html .= "<a href=\"{$url}\" target=\"_blank\" title=\"{$nama}\" class=\"inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-300 rounded border border-blue-200 transition shadow-sm\">
                                    <span>📎</span> {$badgeText}
                                </a>";
                            }
                        }
                        $html .= '</div>';

                        return new HtmlString($html);
                    })
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('approvals')
                    ->label('Approval History')
                    ->getStateUsing(function ($record) {
                        return $record->approvals->map(function ($a) {
                            $status = ucfirst($a->status);
                            $nama = $a->user->name ?? 'User';

                            if ($a->status === 'pending') {
                                return "{$nama}: Pending";
                            }

                            $tgl = $a->action_at ? Carbon::parse($a->action_at)->format('Y-m-d') : '-';
                            return "{$nama}: {$status} ({$tgl})";
                        })->implode(', ');
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('plant_id')
                    ->label('Filter by Plant')
                    ->options(function () {
                        return Plant::query()
                            ->get()
                            ->mapWithKeys(function ($plant) {
                                $nama = $plant->nama ? " - {$plant->nama}" : '';
                                return [$plant->id => (string) "{$plant->kode}{$nama}"];
                            })
                            ->toArray();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->disabled(fn ($record) => ! $record->canBeEdited())
                    ->tooltip(fn ($record) => ! $record->canBeEdited() ? 'Dokumen tidak dapat diubah karena sudah ada persetujuan approval / selesai.' : null),

                Tables\Actions\Action::make('tambah_lampiran')
                    ->label('Tambah Berkas')
                    ->icon('heroicon-o-paper-clip')
                    ->color('gray')
                    ->modalHeading('Tambah Lampiran Berkas Baru')
                    ->modalDescription('Unggah dokumen pendukung atau berkas bukti tambahan untuk klaim ini.')
                    ->form([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Berkas Bukti Tambahan')
                            ->directory('claim-attachments')
                            ->disk('public')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->storeFileNamesIn('nama_file_asli')
                            ->required(),
                        Forms\Components\TextInput::make('keterangan')
                            ->label('Keterangan File')
                            ->placeholder('Contoh: Berkas fisik susulan / foto tambahan')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->attachments()->create([
                            'file_path' => $data['file_path'],
                            'keterangan' => $data['keterangan'],
                            'nama_file_asli' => basename($data['file_path']),
                        ]);

                        Notification::make()
                            ->title('Lampiran tambahan berhasil disimpan')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status !== 'rejected'),

                // =========================================================================
                // ACTION APPROVE
                // =========================================================================
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Persetujuan Klaim')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui pengajuan klaim ini?')
                    ->visible(fn ($record) =>
                        $record->status === 'pending' &&
                        $record->isUserCurrentApprover(auth()->user())
                    )
                    ->form([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Approval (Opsional)')
                            ->placeholder('Masukkan catatan tambahan jika ada...')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $record->approve($record->currentStepOrder(), auth()->id(), $data['catatan'] ?? null);

                            // Memicu email notifikasi ke step berikutnya atau pemberitahuan selesai ke pembuat tiket
                            ClaimNotificationService::sendNextApprovalOrCompletionNotification($record);

                            Notification::make()
                                ->title('Klaim Berhasil Di-approve')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Approve: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // =========================================================================
                // ACTION REJECT
                // =========================================================================
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Penolakan Klaim')
                    ->modalDescription('Silakan isi alasan penolakan pengajuan klaim ini.')
                    ->visible(fn ($record) =>
                        $record->status === 'pending' &&
                        $record->isUserCurrentApprover(auth()->user())
                    )
                    ->form([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Alasan Penolakan')
                            ->placeholder('Tulis alasan penolakan klaim...')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $stepOrder = $record->currentStepOrder();
                            $record->reject($stepOrder, auth()->id(), $data['catatan']);

                            // Ambil approval record yang baru ditolak untuk notifikasi email
                            $rejectedApproval = $record->approvals()
                                ->where('step_order', $stepOrder)
                                ->where('user_id', auth()->id())
                                ->first();

                            if ($rejectedApproval) {
                                ClaimNotificationService::sendRejectionNotification($record, $rejectedApproval);
                            }

                            Notification::make()
                                ->title('Klaim Berhasil Ditolak')
                                ->danger()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Reject: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // =========================================================================
                // ACTION CETAK PDF (INDIVIDU)
                // =========================================================================
                Tables\Actions\Action::make('print_pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('danger')
                    ->action(function ($record) {
                        $claims = collect([$record->load(['plant.company', 'asset', 'creator', 'approvals.user', 'attachments'])]);
                        $pdf = Pdf::loadView('pdf.claim-document', compact('claims'))
                            ->setPaper('a4', 'portrait');

                        $sanitizedTicket = str_replace(['/', '\\'], '-', $record->ticket_number);
                        $fileName = "Klaim-{$sanitizedTicket}.pdf";

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, $fileName);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

                // =========================================================================
                // BULK ACTION EXPORT PDF (ARSIP FISIK)
                // =========================================================================
                BulkAction::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function ($records) {
                        $claims = $records->load(['plant.company', 'asset', 'creator', 'approvals.user', 'attachments']);
                        $pdf = Pdf::loadView('pdf.claim-document', compact('claims'))
                            ->setPaper('a4', 'portrait');

                        $fileName = 'Arsip-Klaim-' . now()->format('Y-m-d_His') . '.pdf';

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, $fileName);
                    }),

                // =========================================================================
                // BULK ACTION EXPORT EXCEL (DETAIL TRANSAKSI)
                // =========================================================================
                BulkAction::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->action(function ($records) {
                        $recordIds = $records->pluck('id')->toArray();
                        $fileName = 'Laporan-Klaim-' . now()->format('Y-m-d_His') . '.xlsx';

                        return Excel::download(new ClaimExport($recordIds), $fileName);
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['approvals.user', 'plant', 'asset', 'creator', 'attachments'])
            ->orderByRaw("
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM claim_approvals
                        WHERE claim_approvals.claim_id = claims.id
                        AND claim_approvals.status = 'pending'
                        AND claim_approvals.user_id = ?
                    ) THEN 0
                    ELSE 1
                END ASC", [Auth::id()])
            ->orderBy('created_at', 'desc');
    }

    public static function canEdit(Model $record): bool
    {
        if ($record instanceof Claim && ! $record->canBeEdited()) {
            return false;
        }

        return parent::canEdit($record);
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
            'index' => Pages\ListClaims::route('/'),
            'create' => Pages\CreateClaim::route('/create'),
            'edit' => Pages\EditClaim::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ];
    }
}
