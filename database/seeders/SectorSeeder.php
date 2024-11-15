<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectors = [
            "Aerospace &amp; Defense",
            "Alternative Energy",
            "Automobiles &amp; Parts",
            "Banks",
            "Beverages",
            "Chemicals",
            "Construction &amp; Materials",
            "Electricity",
            "Electronic &amp; Electrical Equipment",
            "Equity Investment Instruments",
            "Financial Services",
            "Fixed Line Telecommunications",
            "Food &amp; Drug Retailers",
            "Food Producers",
            "Forestry &amp; Paper",
            "Gas, Water &amp; Multi-utilities",
            "General Industrials",
            "General Retailers",
            "Health Care Equipment &amp; Services",
            "Household Goods &amp; Home Construction",
            "Industrial Engineering",
            "Industrial Metals &amp; Mining",
            "Industrial Transportation",
            "Leisure Goods",
            "Life Insurance",
            "Media",
            "Mining",
            "Mobile Telecommunications",
            "Nonequity Investment Instruments",
            "Nonlife Insurance",
            "Oil &amp; Gas Producers",
            "Oil Equipment, Services &amp; Distribution",
            "Personal Goods",
            "Pharmaceuticals &amp; Biotechnology",
            "Real Estate Investment &amp; Services"
        ];

        foreach ($sectors as $key => $value) {
            Sector::create(['name' => $value]);
        }
    }
}
