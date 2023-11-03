/**
 * Sets the headers for API fetch requests, including the authentication JWT.
 *
 * @returns object
 */
export const setHeaders = () => {
  return {
    'Content-Type': 'application/json',
    Authorization: sessionStorage.getItem('jwt') || ''
  }
}
