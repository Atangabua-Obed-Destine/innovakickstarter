// Every French and English date name, for one line of text.
import moment from 'moment/min/moment-with-locales'

// "Offers end Sunday 29 November · Fin des offres : dimanche 29 novembre"
export function offerEndText(date: Date): string {
  const english = moment(date).locale('en-gb').format('dddd D MMMM')
  const french = moment(date).locale('fr').format('dddd D MMMM')
  return `Offers end ${english} · Fin des offres : ${french}`
}
