<?php

namespace App\Support;

class PlebisciteCatalog
{
    private const GAMES = [
        ['hades', 'Hades', 1145360], ['witcher-3', 'The Witcher 3: Wild Hunt', 292030],
        ['elden-ring', 'Elden Ring', 1245620], ['stardew-valley', 'Stardew Valley', 413150],
        ['cyberpunk-2077', 'Cyberpunk 2077', 1091500], ['outer-wilds', 'Outer Wilds', 753640],
        ['disco-elysium', 'Disco Elysium', 632470], ['baldurs-gate-3', "Baldur's Gate 3", 1086940],
        ['celeste', 'Celeste', 504230], ['hollow-knight', 'Hollow Knight', 367520],
        ['red-dead-redemption-2', 'Red Dead Redemption 2', 1174180], ['titanfall-2', 'Titanfall 2', 1237970],
        ['dredge', 'DREDGE', 1562430], ['portal-2', 'Portal 2', 620],
        ['undertale', 'Undertale', 391540], ['bg3', 'Baldur’s Gate 3', 1086940],
        ['it-takes-two', 'It Takes Two', 1426210], ['deep-rock-galactic', 'Deep Rock Galactic', 548430],
        ['animal-well', 'Animal Well', 813230], ['silksong', 'Hollow Knight: Silksong', 1030300],
        ['half-life-3', 'Half-Life 3', 70], ['bloodborne', 'Bloodborne (PC)', 1245620],
        ['death-stranding', 'Death Stranding', 1190460], ['goat-simulator-3', 'Goat Simulator 3', 850190],
    ];

    public static function all(): array
    {
        $definitions = [
            ['nieukonczone', 'Najlepsza gra, której nie ukończyłem', 'hades,witcher-3,elden-ring,cyberpunk-2077,disco-elysium,baldurs-gate-3'],
            ['nie-wrocilem', 'Najlepsza gra, do której nie wróciłem po ukończeniu', 'outer-wilds,portal-2,undertale,witcher-3,red-dead-redemption-2,disco-elysium'],
            ['nikt-nie-zagral', 'Najlepsza gra – szkoda, że nikt nie zagrał', 'hades,celeste,dredge,animal-well,deep-rock-galactic,titanfall-2'],
            ['rozczarowanie', 'Największe rozczarowanie', 'cyberpunk-2077,red-dead-redemption-2,elden-ring,stardew-valley,goat-simulator-3,death-stranding'],
            ['niespodzianka', 'Największa niespodzianka', 'hades,dredge,outer-wilds,undertale,animal-well,titanfall-2'],
            ['powinienem-odpalic', 'Gra, którą powinienem odpalić, ale tego nie zrobiłem', 'baldurs-gate-3,elden-ring,disco-elysium,stardew-valley,red-dead-redemption-2,celeste'],
            ['zaczela-swietnie', 'Gra, która zaczęła się świetnie, ale później męczy', 'witcher-3,cyberpunk-2077,elden-ring,red-dead-redemption-2,death-stranding,stardew-valley'],
            ['utrata-czasu', 'Najlepsza gra, przy której straciłem poczucie czasu', 'stardew-valley,elden-ring,baldurs-gate-3,hades,deep-rock-galactic,witcher-3'],
            ['klimacik', 'Najlepsza gra „z klimacikiem”', 'disco-elysium,outer-wilds,dredge,death-stranding,hollow-knight,red-dead-redemption-2'],
            ['najgorsze-studio', 'Najgorsza firma / studio', 'cyberpunk-2077,goat-simulator-3,death-stranding,witcher-3,elden-ring,portal-2'],
            ['wpadka', 'Największa wpadka giereczkowa', 'cyberpunk-2077,goat-simulator-3,death-stranding,red-dead-redemption-2,baldurs-gate-3,titanfall-2'],
            ['goat', 'Największy GOAT giereczkowy', 'witcher-3,elden-ring,portal-2,red-dead-redemption-2,hades,baldurs-gate-3'],
            ['polska-gra', 'Najlepsza polska gra / studio (Polska gurom)', 'witcher-3,cyberpunk-2077,dredge,disco-elysium,stardew-valley,goat-simulator-3'],
            ['cwel', 'Największy cwel', 'elden-ring,portal-2,hades,celeste,undertale,goat-simulator-3'],
            ['friend-slop', 'Najlepszy friend slop', 'it-takes-two,deep-rock-galactic,portal-2,stardew-valley,elden-ring,goat-simulator-3'],
            ['cozy', 'Najlepsza cozy gierka', 'stardew-valley,dredge,animal-well,undertale,hades,celeste'],
            ['arka', 'Kategoria Arka', 'elden-ring,hades,portal-2,deep-rock-galactic,goat-simulator-3,witcher-3'],
            ['pisarski-cenograja', 'Kategoria Pisarskiego / Cenograja', 'death-stranding,disco-elysium,cyberpunk-2077,witcher-3,portal-2,red-dead-redemption-2'],
            ['nowe-stare', 'Najlepsze „już kiedyś w to grałem, ale jest nowe teraz”', 'hades,hollow-knight,portal-2,undertale,witcher-3,stardew-valley'],
            ['moze-dozyje', 'Najlepsza gra „może dożyję”', 'half-life-3,bloodborne,elden-ring,cyberpunk-2077,death-stranding,silksong'],
            ['gramy-baba', 'Najlepsze „gramy baba”', 'stardew-valley,celeste,it-takes-two,disco-elysium,hades,animal-well'],
            ['kojima', 'Kategoria imienia Hideo Kojimy (najlepsze stopy w grach)', 'death-stranding,witcher-3,red-dead-redemption-2,elden-ring,cyberpunk-2077,hades'],
        ];

        $games = collect(self::GAMES)->keyBy(0);

        return collect($definitions)->map(function (array $definition) use ($games) {
            [$key, $title, $slugs] = $definition;

            return [
                'key' => $key,
                'title' => $title,
                'candidates' => collect(explode(',', $slugs))->map(function ($slug) use ($games) {
                    $game = $games->get($slug);

                    return [
                        'key' => $game[0],
                        'title' => $game[1],
                        'image' => "https://cdn.cloudflare.steamstatic.com/steam/apps/{$game[2]}/library_600x900.jpg",
                    ];
                })->values()->all(),
            ];
        })->all();
    }
}
