<?php

namespace App\Console\Commands;

use App\Models\Admission;
use App\Models\AdmissionRoomHistory;
use App\Models\Icu\IcuBed;
use App\Models\Ward\WardRoom;
use Illuminate\Console\Command;

class BackfillAdmissionRoomHistories extends Command
{
    protected $signature = 'app:backfill-admission-room-histories';
    protected $description = 'Create admission_room_histories for admissions created before the table existed';

    public function handle()
    {
        $count = 0;
        $total = 0;

        $admissions = Admission::withoutGlobalScopes()
            ->whereIn('status', ['Admitted', 'Discharged'])
            ->get();

        $bar = $this->output->createProgressBar($admissions->count());
        $bar->start();

        foreach ($admissions as $adm) {
            $existing = AdmissionRoomHistory::where('admission_id', $adm->id)->exists();

            if (!$existing) {
                $roomType = null;
                $roomId = null;
                $roomName = null;
                $wardName = null;
                $roomRate = 0;

                if ($adm->ward_room_id) {
                    $wr = WardRoom::with('ward')->find($adm->ward_room_id);
                    if ($wr) {
                        $roomType = 'ward_room';
                        $roomId = $wr->id;
                        $roomName = $wr->name;
                        $wardName = $wr->ward?->name;
                        $roomRate = (float) ($wr->room_rate ?? 0);
                    }
                } elseif ($adm->icu_bed_id) {
                    $icu = IcuBed::with('ward')->find($adm->icu_bed_id);
                    if ($icu) {
                        $roomType = 'icu_bed';
                        $roomId = $icu->id;
                        $roomName = $icu->name;
                        $wardName = $icu->ward?->name;
                        $roomRate = (float) ($icu->room_rate ?? 0);
                    }
                }

                if ($roomType) {
                    AdmissionRoomHistory::recordAssignment(
                        $adm->id,
                        $roomType,
                        $roomId,
                        $roomName,
                        $wardName,
                        $roomRate,
                        $adm->tenant_id,
                        $adm->created_by ?? 1,
                        $adm->admission_date ?? $adm->created_date ?? now()
                    );

                    if ($adm->status === 'Discharged' && $adm->discharge_date) {
                        AdmissionRoomHistory::closeActive(
                            $adm->id,
                            $adm->discharge_date,
                            $adm->updated_by ?? 1
                        );
                    }

                    $count++;
                }
            }

            $total++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Created {$count} history records out of {$total} admissions checked.");
    }
}
