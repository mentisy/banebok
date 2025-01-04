import { ChangeEvent, FormEvent, useEffect, useState } from "react";
import "./App.css";
import { endOfWeek, format } from "date-fns";
import { nb } from "date-fns/locale";
import { getMatches } from "./Api/Api";
import Matches from "./components/Matches";
import { Match } from "./Api/Entities";

function App() {
    const [loadingState, setLoadingState] = useState(true);

    const [dates, setDates] = useState({
        from: format(new Date(), "y-MM-dd", { locale: nb }),
        to: format(endOfWeek(new Date(), { locale: nb }), "y-MM-dd"),
    });

    const [matches, setMatches] = useState<Match[]>([]);

    const handleDateChange = (event: ChangeEvent<HTMLInputElement>) => {
        const { name, value } = event.target;

        setDates((prevState) => ({
            ...prevState,
            [name]: value,
        }));
    };

    useEffect(() => {
        getMatches(new Date(dates.from), new Date(dates.to)).then((res) => {
            setMatches(res.data.matches);
            setLoadingState(false);
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const handleSubmit = (event: FormEvent<HTMLFormElement>): false => {
        event.preventDefault();
        setLoadingState(true);
        getMatches(new Date(dates.from), new Date(dates.to)).then((res) => {
            setMatches(res.data.matches);
            setLoadingState(false);
        });

        return false;
    };

    return (
        <div className="App">
            <form onSubmit={handleSubmit}>
                <fieldset role="form">
                    <section className="select-flex">
                        <div>
                            <label htmlFor="from">Velg fra dato:</label>
                            <input type="date" value={dates.from} id="from" name="from" onChange={handleDateChange} />
                        </div>
                        <div>
                            <label htmlFor="to">Velg til dato:</label>
                            <input type="date" value={dates.to} id="to" name="to" onChange={handleDateChange} />
                        </div>
                    </section>
                    <button type="submit">Oppdater</button>
                </fieldset>
            </form>
            {loadingState && <span>Laster...</span>}
            {!loadingState && <Matches matches={matches} />}
        </div>
    );
}

export default App;
