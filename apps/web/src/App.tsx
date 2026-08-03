import { useEffect, useState } from 'react'
import './App.css'

type HelloResponse = {
  message: string
}

function App() {
  const [state, setState] = useState<
    { status: 'loading' } | { status: 'error' } | { status: 'ok'; message: string }
  >({ status: 'loading' })

  useEffect(() => {
    fetch('/api/hello')
      .then((response) => response.json() as Promise<HelloResponse>)
      .then((data) => setState({ status: 'ok', message: data.message }))
      .catch(() => setState({ status: 'error' }))
  }, [])

  return (
    <section id="hello">
      <h1>Nulengeo</h1>
      {state.status === 'loading' && <p>Loading...</p>}
      {state.status === 'error' && <p>Could not reach the api.</p>}
      {state.status === 'ok' && <p>{state.message}</p>}
    </section>
  )
}

export default App
