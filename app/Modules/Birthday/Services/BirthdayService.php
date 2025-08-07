<?php

namespace App\Modules\Birthday\Services;

use App\Modules\User\Models\User;
use Illuminate\Support\Collection;

class BirthdayService
{
    public function getTodayBirthdays(): Collection
    {
        try {
            $today = now();
            $todayFormatted = $today->format('m-d');
            
            return User::whereNotNull('birth_date')
                ->where(\DB::raw("DATE_FORMAT(birth_date, '%m-%d')"), $todayFormatted)
                ->limit(3)
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function getUpcomingBirthdays(int $limit = 3): Collection
    {
        try {
            $today = now();
            $todayFormatted = $today->format('m-d');
            
            // Buscar próximos aniversariantes
            $upcomingBirthdays = User::whereNotNull('birth_date')
                ->where(\DB::raw("DATE_FORMAT(birth_date, '%m-%d')"), '>', $todayFormatted)
                ->orderBy(\DB::raw("DATE_FORMAT(birth_date, '%m-%d')"))
                ->limit($limit)
                ->get();

            // Se não houver suficientes, buscar do início do ano
            if ($upcomingBirthdays->count() < $limit) {
                $remaining = $limit - $upcomingBirthdays->count();
                $nextYearBirthdays = User::whereNotNull('birth_date')
                    ->where(\DB::raw("DATE_FORMAT(birth_date, '%m-%d')"), '<', $todayFormatted)
                    ->orderBy(\DB::raw("DATE_FORMAT(birth_date, '%m-%d')"))
                    ->limit($remaining)
                    ->get();
                
                $upcomingBirthdays = $upcomingBirthdays->merge($nextYearBirthdays);
            }

            return $upcomingBirthdays;
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function getTodayBirthdaysCount(): int
    {
        return $this->getTodayBirthdays()->count();
    }

    public function calculateAge(\Carbon\Carbon $birthDate): int
    {
        return $birthDate->age;
    }

    public function getDaysUntilBirthday(\Carbon\Carbon $birthDate): int
    {
        $nextBirthday = $birthDate->setYear(now()->year);
        if ($nextBirthday->isPast()) {
            $nextBirthday->addYear();
        }
        return now()->diffInDays($nextBirthday);
    }
}