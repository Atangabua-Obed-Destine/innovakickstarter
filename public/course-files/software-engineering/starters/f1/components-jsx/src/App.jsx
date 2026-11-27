import BranchNotice from './components/BranchNotice.jsx';
import Footer from './components/Footer.jsx';
import Header from './components/Header.jsx';
import ProductCard from './components/ProductCard.jsx';

// App is the top component. It puts the other components together.
// Each <Something /> below is a component that lives in its own file.
export default function App() {
  return (
    <>
      <Header />
      <main className="shop">
        <h1>Shop online, collect at your branch</h1>
        <BranchNotice />
        <h2>Product of the week</h2>
        <ProductCard />
      </main>
      <Footer />
    </>
  );
}
