import axios from "axios";

export const getMatches = (from: Date, to: Date) => {
    return axios.post(process.env.REACT_APP_API_URL ?? "", { from, to });
};
