// Where the products come from. Set VITE_API_URL in a .env file to use another API.
export const API_URL: string =
  import.meta.env.VITE_API_URL ?? 'https://innovakickstarter.com/api/course/nkwen'
