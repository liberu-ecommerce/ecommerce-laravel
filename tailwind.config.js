import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        'node_modules/preline/dist/*.js',
    ],
    plugins: [
        require('@tailwindcss/forms'),
        require('preline/plugin'),
      ],
}
