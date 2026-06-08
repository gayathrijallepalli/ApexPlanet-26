<?php

class ProfileStrengthService
{
    public static function calculate(array $profile): int
    {
        $score = 0;
        if (!empty($profile['photo'])) {
            $score += 10;
        }
        if (!empty(trim($profile['education'] ?? ''))) {
            $score += 20;
        }
        if (!empty(trim($profile['skills'] ?? ''))) {
            $score += 25;
        }
        if (!empty(trim($profile['experience'] ?? ''))) {
            $score += 25;
        }
        if (!empty($profile['resume_path'])) {
            $score += 20;
        }
        return min(100, $score);
    }

    public static function breakdown(array $profile): array
    {
        return [
            ['label' => 'Profile Photo', 'weight' => 10, 'done' => !empty($profile['photo'])],
            ['label' => 'Education', 'weight' => 20, 'done' => !empty(trim($profile['education'] ?? ''))],
            ['label' => 'Skills', 'weight' => 25, 'done' => !empty(trim($profile['skills'] ?? ''))],
            ['label' => 'Experience', 'weight' => 25, 'done' => !empty(trim($profile['experience'] ?? ''))],
            ['label' => 'Resume Upload', 'weight' => 20, 'done' => !empty($profile['resume_path'])],
        ];
    }
}
