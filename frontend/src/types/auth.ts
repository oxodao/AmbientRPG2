import * as z from 'zod';

export const AuthenticationRequest = z.object({
	username: z.string().nonempty(),
	password: z.string().nonempty(),
});

export type AuthenticationResponse = {
	token: string;
	refresh_token: string;
};

export type TokenUser = {
	id: number;
	iri: string;
	iat: number;
	exp: number;
	roles: string[];
	username: string;
	language: string;
};
