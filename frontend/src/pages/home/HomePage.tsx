import { Link } from 'react-router-dom';
import { Button } from '@/shared/ui/Button';

export function HomePage() {
  return (
    <div className="relative overflow-hidden">
      {/* Hero Section */}
      <section className="relative px-4 sm:px-6 lg:px-8 py-20 sm:py-32">
        {/* Enhanced Background decoration */}
        <div className="absolute inset-0 -z-10 overflow-hidden">
          <div className="absolute left-[calc(50%-4rem)] top-10 -z-10 transform-gpu blur-3xl sm:left-[calc(50%-18rem)] lg:left-48 lg:top-[calc(50%-30rem)] xl:left-[calc(50%-24rem)] animate-float">
            <div className="aspect-[1108/632] w-[69.25rem] bg-gradient-to-r from-primary-500/30 via-purple-500/20 to-accent/30 opacity-30"></div>
          </div>
          <div className="absolute right-[calc(50%-4rem)] bottom-10 -z-10 transform-gpu blur-3xl sm:right-[calc(50%-18rem)] lg:right-48 lg:bottom-[calc(50%-20rem)] xl:right-[calc(50%-24rem)]">
            <div className="aspect-[1108/632] w-[50rem] bg-gradient-to-l from-accent/30 via-pink-500/20 to-primary-500/20 opacity-20 animate-float" style={{animationDelay: '-1.5s'}}></div>
          </div>
        </div>

        <div className="mx-auto max-w-7xl text-center animate-fade-in-up">
          <div className="flex items-center justify-center mb-12 hero-glow">
            <div className="text-8xl animate-float animate-pulse-glow">🎵</div>
          </div>
          
          <h1 className="text-6xl sm:text-7xl lg:text-8xl font-bold tracking-tight text-foreground mb-8 bg-gradient-to-r from-primary-600 via-purple-500 to-primary-400 bg-clip-text text-transparent animate-shimmer">
            SocialMusic
          </h1>
          
          <p className="text-xl sm:text-2xl text-muted-foreground mb-12 max-w-4xl mx-auto leading-relaxed opacity-0 animate-fade-in-up" style={{animationDelay: '0.3s'}}>
            Discover, share, and enjoy music with your friends. Connect through the universal language of music and build your perfect soundtrack.
          </p>
          
          <div className="flex flex-col sm:flex-row gap-6 justify-center mb-20 opacity-0 animate-fade-in-up" style={{animationDelay: '0.6s'}}>
            <Link to="/feed">
              <Button size="lg" className="w-full sm:w-auto min-w-[200px] text-lg py-4 px-8">
                Explore Music Feed
              </Button>
            </Link>
            <Link to="/artists">
              <Button variant="outline" size="lg" className="w-full sm:w-auto min-w-[200px] text-lg py-4 px-8">
                Browse Artists
              </Button>
            </Link>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="px-4 sm:px-6 lg:px-8 py-24 premium-gradient">
        <div className="mx-auto max-w-7xl">
          <div className="text-center mb-20 animate-fade-in-up">
            <h2 className="text-4xl sm:text-5xl font-bold text-foreground mb-6 bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text">
              Everything you need for your music journey
            </h2>
            <p className="text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">
              From discovering new artists to creating the perfect playlist, we've got you covered with premium features.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 stagger-animation">
            <div className="floating-card p-10 text-center group">
              <div className="w-20 h-20 bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 rounded-3xl flex items-center justify-center mb-8 mx-auto group-hover:scale-125 group-hover:rotate-6 transition-all duration-500 shadow-2xl">
                <span className="text-3xl">🎧</span>
              </div>
              <h3 className="text-2xl font-bold text-card-foreground mb-6">Discover Music</h3>
              <p className="text-muted-foreground leading-relaxed text-lg">
                Find new tracks and artists that match your taste with our intelligent AI-powered recommendation system.
              </p>
            </div>

            <div className="floating-card p-10 text-center group">
              <div className="w-20 h-20 bg-gradient-to-br from-success-500 via-success-600 to-emerald-700 rounded-3xl flex items-center justify-center mb-8 mx-auto group-hover:scale-125 group-hover:rotate-6 transition-all duration-500 shadow-2xl">
                <span className="text-3xl">📚</span>
              </div>
              <h3 className="text-2xl font-bold text-card-foreground mb-6">Create Playlists</h3>
              <p className="text-muted-foreground leading-relaxed text-lg">
                Organize your favorite songs into custom playlists and share them with friends seamlessly.
              </p>
            </div>

            <div className="floating-card p-10 text-center group">
              <div className="w-20 h-20 bg-gradient-to-br from-warning-500 via-orange-500 to-red-500 rounded-3xl flex items-center justify-center mb-8 mx-auto group-hover:scale-125 group-hover:rotate-6 transition-all duration-500 shadow-2xl">
                <span className="text-3xl">⭐</span>
              </div>
              <h3 className="text-2xl font-bold text-card-foreground mb-6">Share & Rate</h3>
              <p className="text-muted-foreground leading-relaxed text-lg">
                Share music with friends, rate tracks and albums, and build a vibrant community around music.
              </p>
            </div>

            <div className="floating-card p-10 text-center group md:col-span-2 lg:col-span-1">
              <div className="w-20 h-20 bg-gradient-to-br from-info-500 via-cyan-600 to-blue-700 rounded-3xl flex items-center justify-center mb-8 mx-auto group-hover:scale-125 group-hover:rotate-6 transition-all duration-500 shadow-2xl">
                <span className="text-3xl">👥</span>
              </div>
              <h3 className="text-2xl font-bold text-card-foreground mb-6">Connect</h3>
              <p className="text-muted-foreground leading-relaxed text-lg">
                Follow your favorite artists and connect with other music lovers in our growing community.
              </p>
            </div>

            <div className="floating-card p-10 text-center group">
              <div className="w-20 h-20 bg-gradient-to-br from-purple-500 via-violet-600 to-indigo-700 rounded-3xl flex items-center justify-center mb-8 mx-auto group-hover:scale-125 group-hover:rotate-6 transition-all duration-500 shadow-2xl">
                <span className="text-3xl">🎵</span>
              </div>
              <h3 className="text-2xl font-bold text-card-foreground mb-6">Stream</h3>
              <p className="text-muted-foreground leading-relaxed text-lg">
                Listen to high-quality audio streams and enjoy your music anywhere, anytime with crystal clarity.
              </p>
            </div>

            <div className="floating-card p-10 text-center group">
              <div className="w-20 h-20 bg-gradient-to-br from-pink-500 via-rose-600 to-red-600 rounded-3xl flex items-center justify-center mb-8 mx-auto group-hover:scale-125 group-hover:rotate-6 transition-all duration-500 shadow-2xl">
                <span className="text-3xl">📊</span>
              </div>
              <h3 className="text-2xl font-bold text-card-foreground mb-6">Analytics</h3>
              <p className="text-muted-foreground leading-relaxed text-lg">
                Track your listening habits and discover deep insights about your music preferences and trends.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="relative px-4 sm:px-6 lg:px-8 py-24 overflow-hidden">
        {/* Background effects */}
        <div className="absolute inset-0 bg-gradient-to-br from-primary-500/5 via-purple-500/5 to-accent/5"></div>
        <div className="absolute inset-0 bg-gradient-to-t from-background/50 to-transparent"></div>
        
        <div className="mx-auto max-w-7xl text-center relative z-10">
          <div className="glass-effect p-16 rounded-3xl animate-bounce-in">
            <h2 className="text-4xl sm:text-5xl font-bold text-foreground mb-6 bg-gradient-to-r from-primary-600 via-purple-500 to-primary-400 bg-clip-text text-transparent">
              Ready to start your musical journey?
            </h2>
            <p className="text-xl text-muted-foreground mb-12 max-w-3xl mx-auto leading-relaxed">
              Join thousands of music enthusiasts already using SocialMusic to discover, share, and enjoy music in ways you've never experienced before.
            </p>
            <div className="flex flex-col sm:flex-row gap-6 justify-center items-center">
              <Link to="/login">
                <Button size="lg" className="min-w-[250px] text-lg py-4 px-10 animate-pulse-glow">
                  Get Started Today
                </Button>
              </Link>
              <Link to="/feed">
                <Button variant="outline" size="lg" className="min-w-[250px] text-lg py-4 px-10">
                  Explore Demo
                </Button>
              </Link>
            </div>
            
            {/* Stats */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 pt-16 border-t border-border/20">
              <div className="text-center smooth-bounce">
                <div className="text-3xl font-bold text-primary-600 mb-2">10K+</div>
                <div className="text-muted-foreground">Active Users</div>
              </div>
              <div className="text-center smooth-bounce" style={{animationDelay: '0.1s'}}>
                <div className="text-3xl font-bold text-primary-600 mb-2">50K+</div>
                <div className="text-muted-foreground">Songs Shared</div>
              </div>
              <div className="text-center smooth-bounce" style={{animationDelay: '0.2s'}}>
                <div className="text-3xl font-bold text-primary-600 mb-2">1M+</div>
                <div className="text-muted-foreground">Playlists Created</div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}