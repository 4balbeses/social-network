import { useState } from 'react'
import { useUnit } from 'effector-react'
import { useNavigate } from 'react-router-dom'
import { setUser } from '../../entities/user'
import { Header } from '../../widgets/header'
import { Button } from '../../shared/ui/Button'

export function LoginPage() {
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const navigate = useNavigate()
  const handleSetUser = useUnit(setUser)

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    
    // Mock login logic
    if (username && password) {
      handleSetUser({
        id: 1,
        username,
        fullName: username,
        registeredAt: new Date().toISOString(),
        roles: ['user']
      })
      navigate('/')
    }
  }

  return (
    <div>
      <Header />
      <main className="max-w-md mx-auto px-4 py-12">
        <div className="bg-white p-8 rounded-lg shadow-md">
          <h1 className="text-2xl font-bold text-center mb-6">Login</h1>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label htmlFor="username" className="block text-sm font-medium text-gray-700 mb-1">
                Username
              </label>
              <input
                type="text"
                id="username"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-1">
                Password
              </label>
              <input
                type="password"
                id="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <Button type="submit" className="w-full">
              Login
            </Button>
          </form>
        </div>
      </main>
    </div>
  )
}