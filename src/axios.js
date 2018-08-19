import axios from 'axios'

export const client = axios.create({
  baseURL: 'http://localhost/cubecomps-checker/index.php',
});
