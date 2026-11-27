import { Link } from 'react-router';

export default function NotFoundPage() {
  return (
    <>
      <h1>Page not found</h1>
      <p>This address does not exist. It may be an old link.</p>
      <Link to="/">Go to the shop</Link>
    </>
  );
}
