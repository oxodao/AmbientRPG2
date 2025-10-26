import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { createRouter, RouterProvider } from '@tanstack/react-router';
import dayjs from 'dayjs';
import LocalizedFormat from 'dayjs/plugin/localizedFormat';
import i18n from 'i18next';
import detector from 'i18next-browser-languagedetector';
import Backend from 'i18next-http-backend';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { I18nextProvider, initReactI18next } from 'react-i18next';
import { ThemeProvider } from './components/theme-provider';
import { Toaster } from './components/ui/sonner';
import { TooltipProvider } from './components/ui/tooltip';

import '@fontsource-variable/rubik/index.css';
import './assets/css/index.css';

import { routeTree } from './routeTree.gen';

i18n
	.use(Backend)
	.use(detector)
	.use(initReactI18next)
	.init({
		fallbackLng: 'en',
		interpolation: {
			escapeValue: false,
		},
	});

dayjs.extend(LocalizedFormat);

const queryClient = new QueryClient();
const router = createRouter({ routeTree, context: { queryClient } });

declare module '@tanstack/react-router' {
	interface Register {
		router: typeof router;
	}
}

const root = document.getElementById('root');

if (root) {
	createRoot(root).render(
		<StrictMode>
			<I18nextProvider i18n={i18n}>
				<ThemeProvider defaultTheme='dark' storageKey='theme'>
					<TooltipProvider>
						<QueryClientProvider client={queryClient}>
							<RouterProvider router={router} />
							<Toaster position='top-right' />
						</QueryClientProvider>
					</TooltipProvider>
				</ThemeProvider>
			</I18nextProvider>
		</StrictMode>,
	);
} else {
	document.body.innerHTML = 'No root element found';
}
