import { createContext, useContext, useEffect, useMemo, useReducer } from 'react'
import { seedFacilities, seedGuests, seedReservations, seedRoomTypes } from '../data/seed'

const STORAGE_KEY = 'royal-hotel-v1'

const initial = {
  facilities: seedFacilities,
  roomTypes: seedRoomTypes,
  guests: seedGuests,
  reservations: seedReservations,
}

function load() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (raw) return { ...initial, ...JSON.parse(raw) }
  } catch {
    /* abaikan, pakai data awal */
  }
  return initial
}

const uid = (p) => p + Math.random().toString(36).slice(2, 8)

function reducer(state, action) {
  switch (action.type) {
    case 'guest/save': {
      const g = action.guest
      const exists = state.guests.some((x) => x.id === g.id)
      return {
        ...state,
        guests: exists ? state.guests.map((x) => (x.id === g.id ? g : x)) : [...state.guests, g],
      }
    }
    case 'roomType/add':
      return { ...state, roomTypes: [...state.roomTypes, action.roomType] }
    case 'room/add':
      return {
        ...state,
        roomTypes: state.roomTypes.map((t) =>
          t.id === action.tipeId ? { ...t, kamar: [...t.kamar, action.nomor] } : t,
        ),
      }
    case 'facility/add':
      return { ...state, facilities: [...state.facilities, action.facility] }
    case 'facility/remove':
      return { ...state, facilities: state.facilities.filter((f) => f.id !== action.id) }
    case 'reservation/add':
      return { ...state, reservations: [action.reservation, ...state.reservations] }
    case 'reservation/update':
      return {
        ...state,
        reservations: state.reservations.map((r) =>
          r.kode === action.kode ? { ...r, ...action.patch } : r,
        ),
      }
    case 'reservation/addStatus':
      return {
        ...state,
        reservations: state.reservations.map((r) =>
          r.kode === action.kode
            ? { ...r, status: action.entry.status, riwayat: [...r.riwayat, action.entry] }
            : r,
        ),
      }
    case 'reset':
      return initial
    default:
      return state
  }
}

const Ctx = createContext(null)

export function HotelProvider({ children }) {
  const [state, dispatch] = useReducer(reducer, undefined, load)

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
    } catch {
      /* penyimpanan penuh / diblokir */
    }
  }, [state])

  const value = useMemo(() => {
    const guestById = (id) => state.guests.find((g) => g.id === id)
    const typeById = (id) => state.roomTypes.find((t) => t.id === id)

    const nextKode = () => {
      const max = state.reservations.reduce((m, r) => Math.max(m, Number(r.kode.split('-')[1]) || 0), 0)
      return 'BK-' + String(max + 1).padStart(4, '0')
    }

    return {
      ...state,
      guestById,
      typeById,
      findGuestByNik: (nik) => state.guests.find((g) => g.nik === nik),
      saveGuest: (data) => {
        const guest = { ...data, id: data.id || uid('g') }
        dispatch({ type: 'guest/save', guest })
        return guest
      },
      addRoomType: (data) => {
        const roomType = { ...data, id: uid('t'), kamar: data.kamar || [] }
        dispatch({ type: 'roomType/add', roomType })
        return roomType
      },
      addRoom: (tipeId, nomor) => dispatch({ type: 'room/add', tipeId, nomor }),
      addFacility: (data) => dispatch({ type: 'facility/add', facility: { ...data, id: uid('f') } }),
      removeFacility: (id) => dispatch({ type: 'facility/remove', id }),
      addReservation: (data) => {
        const now = new Date().toISOString().slice(0, 16)
        const reservation = {
          kamar: '-',
          pembayaran: 'Belum bayar',
          status: 'Menunggu',
          ...data,
          kode: nextKode(),
          dibuat: now,
          riwayat: [{ waktu: now, status: 'Menunggu', catatan: 'Booking dibuat via website' }],
        }
        dispatch({ type: 'reservation/add', reservation })
        return reservation
      },
      updateReservation: (kode, patch) => dispatch({ type: 'reservation/update', kode, patch }),
      addStatus: (kode, entry) => dispatch({ type: 'reservation/addStatus', kode, entry }),
      resetData: () => dispatch({ type: 'reset' }),
    }
  }, [state])

  return <Ctx.Provider value={value}>{children}</Ctx.Provider>
}

export function useHotel() {
  const ctx = useContext(Ctx)
  if (!ctx) throw new Error('useHotel harus dipakai di dalam <HotelProvider>')
  return ctx
}
