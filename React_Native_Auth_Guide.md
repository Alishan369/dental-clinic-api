# Persistent Authentication in React Native

This guide demonstrates how to properly store the Sanctum token using `AsyncStorage` (or `SecureStore`) and use Axios Interceptors to attach it to all future API requests.

## 1. Install Dependencies
```bash
npm install @react-native-async-storage/async-storage axios
```

## 2. API Setup & Interceptors (`api.js`)

Create a centralized `api.js` file to handle all backend communication:

```javascript
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const api = axios.create({
    baseURL: 'http://YOUR_API_DOMAIN/api/v1',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// Interceptor to inject Token
api.interceptors.request.use(
    async (config) => {
        const token = await AsyncStorage.getItem('user_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Interceptor to handle 401 Unauthorized (Log out user if token expires)
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response && error.response.status === 401) {
            await AsyncStorage.removeItem('user_token');
            // Redirect to login screen here or dispatch logout action
        }
        return Promise.reject(error);
    }
);

export default api;
```

## 3. Login Functionality

```javascript
import api from './api';
import AsyncStorage from '@react-native-async-storage/async-storage';

const handleLogin = async (email, password) => {
    try {
        const response = await api.post('/auth/login', { email, password });
        
        if (response.data.success) {
            // Save Token
            await AsyncStorage.setItem('user_token', response.data.data.token);
            // Save User Info (Optional)
            await AsyncStorage.setItem('user_info', JSON.stringify(response.data.data.user));
            
            // Redirect to Dashboard
            navigation.replace('Dashboard');
        } else {
             alert(response.data.message); // Will show "Account is not accepted" etc.
        }
    } catch (error) {
        alert(error.response?.data?.message || 'Login failed');
    }
}
```

## 4. App Initialization (Check Token on Load)

In your main `App.js` or `Navigation.js`:

```javascript
import React, { useEffect, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';

const AppNavigator = () => {
    const [isLoading, setIsLoading] = useState(true);
    const [userToken, setUserToken] = useState(null);

    useEffect(() => {
        const checkLoginState = async () => {
            const token = await AsyncStorage.getItem('user_token');
            setUserToken(token);
            setIsLoading(false);
        };
        checkLoginState();
    }, []);

    if (isLoading) {
        return <LoadingScreen />;
    }

    return (
        <NavigationContainer>
            {userToken ? <AppStack /> : <AuthStack />}
        </NavigationContainer>
    );
};
```
