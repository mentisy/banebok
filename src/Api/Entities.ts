export interface Match {
    matchId: number;
    date: DateInterface;
    day: string;
    time: string;
    homeTeam: string;
    result: string;
    awayTeam: string;
    pitch: string;
    tournament: string;
    playType: string;
}

interface DateInterface {
    date: string;
    timezone: string;
    timezone_type: number;
}
