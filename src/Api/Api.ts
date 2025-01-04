import axios from "axios";

export const getMatches = (from: Date, to: Date) => {
    return axios.post(import.meta.env.VITE_API_URL ?? "", { from, to });
};
