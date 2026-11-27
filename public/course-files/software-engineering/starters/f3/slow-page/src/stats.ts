// November's sales, for the "happy customers" number on the home page.
export type Sale = { phone: string; productId: number; quantity: number }

// Makes the same pretend month of sales on every computer (a real shop would read them from its database).
export function makeSales(count: number): Sale[] {
  let seed = 11
  const random = () => (seed = (seed * 16807) % 2147483647) / 2147483647
  const sales: Sale[] = []
  for (let i = 0; i < count; i++) {
    const customer = Math.floor(random() * 20000)
    sales.push({
      phone: '6' + String(70000000 + customer * 37).padStart(8, '0'),
      productId: 1 + Math.floor(random() * 20),
      quantity: 1 + Math.floor(random() * 4),
    })
  }
  return sales
}

// How many different customers (phone numbers) bought something.
export function uniqueCustomers(sales: Sale[]): number {
  const unique = sales.filter((sale, index) => sales.findIndex((other) => other.phone === sale.phone) === index)
  return unique.length
}
