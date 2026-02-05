<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title("Qo'llanma")]
class Help extends Component
{
    #[Url]
    public string $section = '';

    public function mount(): void
    {
        if (empty($this->section)) {
            $this->section = $this->getDefaultSection();
        }
    }

    public function getDefaultSection(): string
    {
        $user = auth()->user();

        if ($user->hasRole('manager')) {
            return 'dashboard';
        }

        if ($user->hasRole('teacher')) {
            return 'teacher-dashboard';
        }

        if ($user->hasRole('student')) {
            return 'student-dashboard';
        }

        return 'general';
    }

    public function getSections(): array
    {
        $user = auth()->user();
        $sections = [];

        if ($user->hasRole('manager')) {
            $sections = [
                'dashboard' => [
                    'title' => 'Dashboard',
                    'icon' => 'home',
                    'content' => $this->getManagerDashboardHelp(),
                ],
                'leads' => [
                    'title' => 'Leadlar',
                    'icon' => 'inbox',
                    'content' => $this->getLeadsHelp(),
                ],
                'students' => [
                    'title' => "O'quvchilar",
                    'icon' => 'user-group',
                    'content' => $this->getStudentsHelp(),
                ],
                'groups' => [
                    'title' => 'Guruhlar',
                    'icon' => 'academic-cap',
                    'content' => $this->getGroupsHelp(),
                ],
                'attendance' => [
                    'title' => 'Davomat',
                    'icon' => 'clipboard-document-check',
                    'content' => $this->getAttendanceHelp(),
                ],
                'schedule' => [
                    'title' => 'Dars jadvali',
                    'icon' => 'calendar-days',
                    'content' => $this->getScheduleHelp(),
                ],
                'debts' => [
                    'title' => 'Qarzdorliklar',
                    'icon' => 'exclamation-triangle',
                    'content' => $this->getDebtsHelp(),
                ],
                'expenses' => [
                    'title' => 'Xarajatlar',
                    'icon' => 'arrow-trending-down',
                    'content' => $this->getExpensesHelp(),
                ],
                'reports' => [
                    'title' => 'Hisobotlar',
                    'icon' => 'chart-bar',
                    'content' => $this->getReportsHelp(),
                ],
                'teachers' => [
                    'title' => 'Ustozlar',
                    'icon' => 'users',
                    'content' => $this->getTeachersHelp(),
                ],
                'courses' => [
                    'title' => "Yo'nalishlar",
                    'icon' => 'book-open-text',
                    'content' => $this->getCoursesHelp(),
                ],
                'rooms' => [
                    'title' => 'Xonalar',
                    'icon' => 'layout-grid',
                    'content' => $this->getRoomsHelp(),
                ],
                'discounts' => [
                    'title' => 'Chegirmalar',
                    'icon' => 'receipt-percent',
                    'content' => $this->getDiscountsHelp(),
                ],
            ];
        }

        if ($user->hasRole('teacher')) {
            $sections = [
                'teacher-dashboard' => [
                    'title' => 'Dashboard',
                    'icon' => 'home',
                    'content' => $this->getTeacherDashboardHelp(),
                ],
                'teacher-schedule' => [
                    'title' => 'Dars jadvali',
                    'icon' => 'calendar-days',
                    'content' => $this->getTeacherScheduleHelp(),
                ],
                'teacher-attendance' => [
                    'title' => 'Davomat',
                    'icon' => 'clipboard-document-check',
                    'content' => $this->getTeacherAttendanceHelp(),
                ],
                'teacher-finance' => [
                    'title' => 'Hisobim',
                    'icon' => 'wallet',
                    'content' => $this->getTeacherFinanceHelp(),
                ],
            ];
        }

        if ($user->hasRole('student')) {
            $sections = [
                'student-dashboard' => [
                    'title' => 'Dashboard',
                    'icon' => 'home',
                    'content' => $this->getStudentDashboardHelp(),
                ],
                'student-schedule' => [
                    'title' => 'Dars jadvali',
                    'icon' => 'calendar-days',
                    'content' => $this->getStudentScheduleHelp(),
                ],
                'student-payments' => [
                    'title' => "To'lovlar",
                    'icon' => 'banknotes',
                    'content' => $this->getStudentPaymentsHelp(),
                ],
            ];
        }

        return $sections;
    }

    public function getCurrentSection(): array
    {
        $sections = $this->getSections();

        return $sections[$this->section] ?? [
            'title' => "Qo'llanma",
            'icon' => 'question-mark-circle',
            'content' => "Bo'limni tanlang.",
        ];
    }

    private function heading(string $text): string
    {
        return '<h4 class="font-semibold text-zinc-900 dark:text-zinc-100 mt-6 mb-2">'.$text.'</h4>';
    }

    private function paragraph(string $text): string
    {
        return '<p class="mb-3">'.$text.'</p>';
    }

    private function list(array $items): string
    {
        $html = '<ul class="list-disc list-inside space-y-1 mb-4">';
        foreach ($items as $item) {
            $html .= '<li>'.$item.'</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function orderedList(array $items): string
    {
        $html = '<ol class="list-decimal list-inside space-y-1 mb-4">';
        foreach ($items as $item) {
            $html .= '<li>'.$item.'</li>';
        }
        $html .= '</ol>';

        return $html;
    }

    // Manager Help Content
    private function getManagerDashboardHelp(): string
    {
        return $this->paragraph('Dashboard - bu tizimning asosiy sahifasi bo\'lib, barcha muhim statistikalarni bir joyda ko\'rsatadi.').
            $this->heading('Statistikalar:').
            $this->list([
                '<strong>Faol o\'quvchilar</strong> - hozirda guruhlarda o\'qiyotgan o\'quvchilar soni',
                '<strong>Kutayotganlar</strong> - guruhga qo\'shilishni kutayotgan o\'quvchilar',
                '<strong>Faol guruhlar</strong> - hozirda dars o\'tayotgan guruhlar soni',
                '<strong>Yangi lidlar</strong> - hali bog\'lanilmagan potensial mijozlar',
                '<strong>Oylik daromad</strong> - joriy oyda tushgan to\'lovlar summasi',
                '<strong>Sof foyda</strong> - daromaddan ustoz ulushi va xarajatlar ayirilgani',
            ]).
            $this->heading('Bugungi darslar:').
            $this->paragraph('Bugun bo\'ladigan barcha darslar ro\'yxati - vaqti, guruhi, xonasi va ustozi ko\'rsatiladi.').
            $this->heading('So\'nggi to\'lovlar:').
            $this->paragraph('Oxirgi 5 ta qabul qilingan to\'lovlar - kim to\'ladi, qancha, qaysi guruh uchun.');
    }

    private function getLeadsHelp(): string
    {
        return $this->paragraph('Leadlar - bu o\'quv markazga qiziqish bildirgan, lekin hali o\'quvchi bo\'lmagan potensial mijozlar.').
            $this->heading('Lead statuslari:').
            $this->list([
                '<strong>Yangi</strong> - hali bog\'lanilmagan',
                '<strong>Bog\'lanildi</strong> - telefon qilindi, javob berdi',
                '<strong>Qiziqqan</strong> - kelishga tayyor, sinov darsiga kutilmoqda',
                '<strong>Konvertatsiya</strong> - o\'quvchiga aylandi',
                '<strong>Yo\'qolgan</strong> - bog\'lanib bo\'lmadi yoki rad etdi',
            ]).
            $this->heading('Yangi lead qo\'shish:').
            $this->orderedList([
                '"+ Yangi lead" tugmasini bosing',
                'Ism, telefon va qiziqtirgan yo\'nalishni kiriting',
                'Izoh bo\'lsa yozing (ixtiyoriy)',
                '"Saqlash" tugmasini bosing',
            ]).
            $this->heading('Leadni o\'quvchiga aylantirish:').
            $this->paragraph('Lead kartasida "Konvertatsiya" tugmasini bosing. Lead avtomatik o\'quvchiga aylanadi va statusı "Konvertatsiya" bo\'ladi.');
    }

    private function getStudentsHelp(): string
    {
        return $this->paragraph('O\'quvchilar bo\'limida barcha ro\'yxatga olingan o\'quvchilarni boshqarishingiz mumkin.').
            $this->heading('O\'quvchi qidirish:').
            $this->paragraph('Qidiruv maydoniga ism yoki telefon raqamini kiriting. Natijalar avtomatik filtlranadi.').
            $this->heading('Yangi o\'quvchi qo\'shish:').
            $this->orderedList([
                '"+ Yangi o\'quvchi" tugmasini bosing',
                'Ism, telefon va tug\'ilgan sanani kiriting',
                'Agar ota-ona telefoni boshqa bo\'lsa, kiriting',
                '"Saqlash" tugmasini bosing',
            ]).
            $this->heading('O\'quvchi profili:').
            $this->paragraph('O\'quvchi ustiga bosib uning to\'liq profilini ko\'ring:').
            $this->list([
                'Shaxsiy ma\'lumotlar',
                'Qatnashayotgan guruhlar',
                'To\'lovlar tarixi',
                'Davomat statistikasi',
                'Chegirmalar',
            ]).
            $this->heading('Guruhga qo\'shish:').
            $this->paragraph('O\'quvchi profilida "Guruhga qo\'shish" tugmasini bosing va kerakli guruhni tanlang.');
    }

    private function getGroupsHelp(): string
    {
        return $this->paragraph('Guruhlar - bu o\'quv jarayonining asosiy birligi. Har bir guruhda ma\'lum yo\'nalish, ustoz va dars vaqti belgilangan.').
            $this->heading('Guruh statuslari:').
            $this->list([
                '<strong>Kutilmoqda</strong> - guruh to\'planmoqda, darslar boshlanmagan',
                '<strong>Faol</strong> - darslar davom etmoqda',
                '<strong>Tugatilgan</strong> - kurs yakunlangan',
            ]).
            $this->heading('Dars kunlari:').
            $this->list([
                '<strong>Toq kunlar</strong> - Dushanba, Chorshanba, Juma',
                '<strong>Juft kunlar</strong> - Seshanba, Payshanba, Shanba',
            ]).
            $this->heading('Yangi guruh ochish:').
            $this->orderedList([
                '"+ Yangi guruh" tugmasini bosing',
                'Yo\'nalish, ustoz va xonani tanlang',
                'Dars kunlari va vaqtini belgilang',
                'Boshlanish sanasini kiriting',
                '"Saqlash" tugmasini bosing',
            ]).
            $this->heading('Guruh tafsilotlari:').
            $this->paragraph('Guruh ustiga bosib quyidagilarni ko\'ring va boshqaring:').
            $this->list([
                'O\'quvchilar ro\'yxati',
                'Yangi o\'quvchi qo\'shish',
                'To\'lovlarni qabul qilish',
                'Davomat belgilash',
            ]);
    }

    private function getAttendanceHelp(): string
    {
        return $this->paragraph('Davomat bo\'limida barcha guruhlar uchun davomat holatini ko\'rish va belgilash mumkin.').
            $this->heading('Davomat belgilash:').
            $this->orderedList([
                'Guruhni tanlang',
                'Sanani tanlang (default: bugun)',
                'Har bir o\'quvchi uchun "Keldi" yoki "Kelmadi" belgilang',
                'O\'zgarishlar avtomatik saqlanadi',
            ]).
            $this->heading('Davomat statistikasi:').
            $this->paragraph('Har bir o\'quvchining umumiy davomat foizi ko\'rsatiladi. Bu ma\'lumot o\'quvchi profilida ham mavjud.').
            $this->heading('Eslatma:').
            $this->paragraph('Davomat faqat dars kunlarida belgilanishi kerak. Tizim avtomatik ravishda faqat guruhning dars kunlarini ko\'rsatadi.');
    }

    private function getScheduleHelp(): string
    {
        return $this->paragraph('Dars jadvali sahifasida haftalik darslar jadvalini ko\'rish mumkin.').
            $this->heading('Jadval ko\'rinishi:').
            $this->list([
                'Darslar vaqt bo\'yicha tartiblangan',
                'Har bir dars uchun guruh nomi, ustoz va xona ko\'rsatilgan',
                'Toq va juft kunlar alohida ko\'rsatiladi',
            ]).
            $this->heading('Filtrlash:').
            $this->paragraph('Ustozlar yoki xonalar bo\'yicha filtrlash mumkin - bu xona bandligini tekshirishda foydali.');
    }

    private function getDebtsHelp(): string
    {
        return $this->paragraph('Qarzdorliklar bo\'limida to\'lanmagan oylik to\'lovlar va o\'tgan qarzlar ko\'rsatiladi.').
            $this->heading('Qarzdorlik turlari:').
            $this->list([
                '<strong>Joriy oy qarzi</strong> - bu oyda to\'lashi kerak, lekin hali to\'lamagan',
                '<strong>O\'tgan qarzlar</strong> - oldingi oylardan qolgan qarzlar',
            ]).
            $this->heading('Qarzni yopish:').
            $this->paragraph('O\'quvchi profiliga o\'tib, to\'lov qabul qiling. To\'lov avtomatik ravishda qarzga yoziladi.').
            $this->heading('Eslatma yuborish:').
            $this->paragraph('Qarzdor o\'quvchilarga SMS eslatma yuborish mumkin.');
    }

    private function getExpensesHelp(): string
    {
        return $this->paragraph('Xarajatlar bo\'limida o\'quv markaz xarajatlarini qayd etish mumkin.').
            $this->heading('Xarajat turlari:').
            $this->list([
                'Ijara',
                'Kommunal to\'lovlar',
                'O\'quv materiallari',
                'Marketing',
                'Boshqa',
            ]).
            $this->heading('Yangi xarajat qo\'shish:').
            $this->orderedList([
                '"+ Yangi xarajat" tugmasini bosing',
                'Turini tanlang',
                'Summani kiriting',
                'Oyni tanlang',
                'Izoh yozing (ixtiyoriy)',
                '"Saqlash" tugmasini bosing',
            ]);
    }

    private function getReportsHelp(): string
    {
        return $this->paragraph('Hisobotlar bo\'limida o\'quv markaz faoliyati bo\'yicha batafsil statistikalarni ko\'rish mumkin.').
            $this->heading('Mavjud hisobotlar:').
            $this->list([
                '<strong>Moliyaviy hisobot</strong> - daromad, xarajat, sof foyda',
                '<strong>O\'quvchilar hisoboti</strong> - yangi, aktiv, tark etganlar',
                '<strong>Guruhlar hisoboti</strong> - to\'ldirish darajasi',
                '<strong>Ustozlar hisoboti</strong> - ish samaradorligi',
            ]).
            $this->heading('Davr tanlash:').
            $this->paragraph('Hisobotlarni oy yoki chorak bo\'yicha filtrlash mumkin.');
    }

    private function getTeachersHelp(): string
    {
        return $this->paragraph('Ustozlar bo\'limida o\'qituvchilarni boshqarish mumkin.').
            $this->heading('Yangi ustoz qo\'shish:').
            $this->orderedList([
                '"+ Yangi ustoz" tugmasini bosing',
                'Ism va telefon kiriting',
                'Foiz stavkasini belgilang (default: 40%)',
                '"Saqlash" tugmasini bosing',
            ]).
            $this->heading('Foiz stavkasi:').
            $this->paragraph('Bu - ustoz oladigan to\'lov ulushi. Masalan, 40% bo\'lsa, har bir to\'lovdan 40% ustozga ketadi.').
            $this->heading('Ustoz kabineti:').
            $this->paragraph('Har bir ustozga login beriladi. U o\'z kabinetida guruhlarini, davomat va daromadini ko\'ra oladi.');
    }

    private function getCoursesHelp(): string
    {
        return $this->paragraph('Yo\'nalishlar - bu o\'quv markazda o\'qitiladigan fanlar yoki kurslar.').
            $this->heading('Yangi yo\'nalish qo\'shish:').
            $this->orderedList([
                '"+ Yangi yo\'nalish" tugmasini bosing',
                'Nomini kiriting (masalan: "Ingliz tili")',
                'Oylik narxini belgilang',
                '"Saqlash" tugmasini bosing',
            ]).
            $this->heading('Narx o\'zgartirish:').
            $this->paragraph('Yo\'nalish narxini o\'zgartirsangiz, bu faqat yangi guruhlarga ta\'sir qiladi. Mavjud guruhlar eski narxda davom etadi.');
    }

    private function getRoomsHelp(): string
    {
        return $this->paragraph('Xonalar bo\'limida o\'quv xonalarini boshqarish mumkin.').
            $this->heading('Yangi xona qo\'shish:').
            $this->orderedList([
                '"+ Yangi xona" tugmasini bosing',
                'Xona nomini kiriting (masalan: "101-xona")',
                'Sig\'imini belgilang (nechta o\'quvchi sig\'adi)',
                '"Saqlash" tugmasini bosing',
            ]).
            $this->heading('Xona bandligi:').
            $this->paragraph('Dars jadvalida xonalar bo\'yicha filtrlash orqali xona bandligini tekshiring.');
    }

    private function getDiscountsHelp(): string
    {
        return $this->paragraph('Chegirmalar tizimi orqali o\'quvchilarga turli chegirmalar berish mumkin.').
            $this->heading('Chegirma turlari:').
            $this->list([
                '<strong>Foizli</strong> - oylik to\'lovdan ma\'lum foiz chegirma',
                '<strong>Qat\'iy summa</strong> - belgilangan summa chegirma',
            ]).
            $this->heading('Chegirma berish:').
            $this->orderedList([
                'O\'quvchi profiliga o\'ting',
                '"Chegirma qo\'shish" tugmasini bosing',
                'Chegirma turini tanlang',
                'Amal qilish muddatini belgilang',
                '"Saqlash" tugmasini bosing',
            ]).
            $this->heading('Chegirmalar kombinatsiyasi:').
            $this->paragraph('Bir o\'quvchida bir nechta chegirma bo\'lishi mumkin - ular jamlanadi.');
    }

    // Teacher Help Content
    private function getTeacherDashboardHelp(): string
    {
        return $this->paragraph('Dashboard sahifasida sizning guruhlaringiz va bugungi darslar haqida ma\'lumot ko\'rsatiladi.').
            $this->heading('Ko\'rsatkichlar:').
            $this->list([
                '<strong>Faol guruhlar</strong> - hozirda dars berayotgan guruhlaringiz',
                '<strong>Jami o\'quvchilar</strong> - barcha guruhlaringizdagi o\'quvchilar soni',
                '<strong>Oylik daromad</strong> - joriy oydagi hisoblangan daromadingiz',
            ]).
            $this->heading('Bugungi darslar:').
            $this->paragraph('Bugun bo\'ladigan darslaringiz ro\'yxati - vaqti, guruhi va xonasi ko\'rsatiladi.');
    }

    private function getTeacherScheduleHelp(): string
    {
        return $this->paragraph('Dars jadvali sahifasida haftalik darslaringizni ko\'rishingiz mumkin.').
            $this->heading('Jadval tuzilishi:').
            $this->list([
                '<strong>Toq kunlar</strong> - Dushanba, Chorshanba, Juma',
                '<strong>Juft kunlar</strong> - Seshanba, Payshanba, Shanba',
            ]).
            $this->heading('Guruh tafsilotlari:').
            $this->paragraph('Guruh ustiga bosib, o\'quvchilar ro\'yxati va boshqa ma\'lumotlarni ko\'ring.');
    }

    private function getTeacherAttendanceHelp(): string
    {
        return $this->paragraph('Davomat sahifasida o\'z guruhlaringiz uchun davomat belgilashingiz mumkin.').
            $this->heading('Davomat belgilash:').
            $this->orderedList([
                'Guruhni tanlang',
                'Sanani tanlang (default: bugun)',
                'Har bir o\'quvchi uchun "Keldi" yoki "Kelmadi" belgilang',
                'O\'zgarishlar avtomatik saqlanadi',
            ]).
            $this->heading('Muhim:').
            $this->paragraph('Davomat faqat dars kunlarida belgilanishi kerak.');
    }

    private function getTeacherFinanceHelp(): string
    {
        return $this->paragraph('Hisobim sahifasida oylik daromadingizni ko\'rishingiz mumkin.').
            $this->heading('Daromad hisoblash:').
            $this->paragraph('Daromadingiz quyidagicha hisoblanadi:').
            $this->list([
                'Har bir to\'lovdan sizning foizingiz (masalan 40%)',
                'Faqat to\'langan summalardan hisoblanadi',
            ]).
            $this->heading('Oylik tafsilot:').
            $this->paragraph('Oyni tanlab, qaysi o\'quvchilardan qancha kelganini ko\'ring.');
    }

    // Student Help Content
    private function getStudentDashboardHelp(): string
    {
        return $this->paragraph('Dashboard sahifasida sizning o\'qishingiz haqida umumiy ma\'lumot ko\'rsatiladi.').
            $this->heading('Ko\'rsatkichlar:').
            $this->list([
                '<strong>Faol guruhlar</strong> - hozirda qatnashayotgan guruhlaringiz',
                '<strong>Davomat foizi</strong> - umumiy darsga kelish foizingiz',
            ]).
            $this->heading('Bugungi darslar:').
            $this->paragraph('Bugun bo\'ladigan darslaringiz ro\'yxati - vaqti, yo\'nalishi va xonasi ko\'rsatiladi.').
            $this->heading('So\'nggi to\'lovlar:').
            $this->paragraph('Oxirgi to\'lovlaringiz tarixi.');
    }

    private function getStudentScheduleHelp(): string
    {
        return $this->paragraph('Dars jadvali sahifasida haftalik darslaringizni ko\'rishingiz mumkin.').
            $this->heading('Jadval tuzilishi:').
            $this->list([
                '<strong>Toq kunlar</strong> - Dushanba, Chorshanba, Juma',
                '<strong>Juft kunlar</strong> - Seshanba, Payshanba, Shanba',
            ]).
            $this->heading('Dars ma\'lumotlari:').
            $this->paragraph('Har bir dars uchun yo\'nalish, ustoz va xona ko\'rsatiladi.');
    }

    private function getStudentPaymentsHelp(): string
    {
        return $this->paragraph('To\'lovlar sahifasida barcha to\'lovlaringiz tarixini ko\'rishingiz mumkin.').
            $this->heading('To\'lov ma\'lumotlari:').
            $this->list([
                'To\'lov sanasi',
                'Qaysi guruh uchun',
                'Qaysi oy uchun',
                'Summa',
            ]).
            $this->heading('Qarzdorlik:').
            $this->paragraph('Agar joriy oy uchun to\'lov qilinmagan bo\'lsa, bu yerda ko\'rsatiladi.');
    }

    public function render()
    {
        return view('livewire.help', [
            'sections' => $this->getSections(),
            'currentSection' => $this->getCurrentSection(),
        ]);
    }
}
