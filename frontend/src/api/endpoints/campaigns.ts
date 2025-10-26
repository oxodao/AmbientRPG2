import type { Collection } from "@/types";
import type { Campaign } from "@/types/campaigns";
import type { SDK } from "..";

export default class Campaigns {
    private sdk: SDK;

    constructor(sdk: SDK) {
        this.sdk = sdk;
    }

    async getCollection(page?: number) {
        page = page || 1;

        return await this.sdk.get<Collection<Campaign>>(`/api/campaigns?page=${page}`);
    }

    async get(id: number) {
        return await this.sdk.get<Campaign>(`/api/campaigns/${id}`);
    }
}