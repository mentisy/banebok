import { Match } from "../Api/Entities";
import { format } from "date-fns";
import { nb } from "date-fns/locale";

interface MatchesInterface {
    matches: Match[];
}

export default function Matches({ matches }: MatchesInterface) {
    if (matches.length === 0) {
        return <span>Ingen kamper</span>;
    }

    const renderedMatches = matches.map((match) => (
        <tr key={match.matchId}>
            <td>{format(new Date(match.date.date), "d. MMMM yyyy", { locale: nb })}</td>
            <td>{match.day}</td>
            <td>{match.time}</td>
            <td>{match.homeTeam}</td>
            <td>{match.awayTeam}</td>
            <td>{match.pitch}</td>
            <td>{match.tournament}</td>
            <td>{match.playType}</td>
        </tr>
    ));

    return (
        <section role="table" className="matches-table-responsive">
            <table className="matches-table">
                <thead>
                    <tr>
                        <th>Dato</th>
                        <th>Dag</th>
                        <th>Tid</th>
                        <th>Hjemmelag</th>
                        <th>Bortelag</th>
                        <th>Bane</th>
                        <th>Turnering</th>
                        <th>Spillform</th>
                    </tr>
                </thead>
                <tbody>{renderedMatches}</tbody>
            </table>
        </section>
    );
}
