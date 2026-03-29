tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: '#3B82F6', // Standardized Blue-500
                primaryLight: '#eff6ff', // Blue-50
                primaryDark: '#1e40af', // Blue-800
                dark: '#1f2937', // Gray-800
                bgBody: '#f9fafb', // Gray-50
            },
            fontFamily: {
                'sans': ['Poppins', 'sans-serif'], // Default sans stack
                'serif': ['Merriweather', 'serif'], // For headers
                'poppins': ['Poppins', 'sans-serif'], // Explicit utility
            },
            boxShadow: {
                'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
            }
        }
    }
}
