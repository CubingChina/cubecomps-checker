import axios from 'axios'

export const client = axios.create({
  baseURL: location.protocol + '//' + location.hostname + '/api/',
});
