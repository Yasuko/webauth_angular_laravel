import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import {
    convertCredentialCreateOptions,
    convertCredentialRequestOptions,
} from '../_lib_service/fido/credential.service';

import {
    fadeInAnimation
} from '../_lib_service/index_helper';
@Component({
    selector: 'app-home',
    templateUrl: './home.component.html',
    styleUrls: [
        './home.component.scss'
    ],
    animations: [
        fadeInAnimation
    ]

})

export class HomeComponent implements OnInit {

    public isLoading = 'default';

    public forms    = {
        userName:       '',
        clossPlatform:   true,
        builtIn:        false
    }

    private server = 'https://localhost/';
    private path = 'webauth_angular_laravel/server/public/';

    private apis    = {
        'registFirst':      'api/registration/start',
        'registFinish':     'api/registration/finish',
        'loginFirst':       'api/assertion/start',
        'loginFinish':      'api/assertion/finish',
    }

    constructor(
        private readonly httpClient: HttpClient,
    ) {
    }

    ngOnInit(): void {
        // this.setup();
    }
    public checkForm(): boolean
    {
        if (this.forms.userName.length >= 2
        ) {
            return true;
        } else {
            return false;
        }
    }
    // ログイン開始
    public doLogin(): void
    {
        this.firstContact(
            this.server + this.path + this.apis.loginFirst,
            'login'
        );
    }

    // ユーザー登録開始
    public doRegist(): void
    {
        this.firstContact(
            this.server + this.path + this.apis.registFirst,
            'regist'
        );
    }

    /**
     * 
     * @param url 
     * @param job 
     */
    private firstContact(url: string, job: string): void
    {
        const request = {
                username: this.forms.userName,
                userVerifivation: 'required',
        };

        this.isLoading = 'loading';
        this.httpClient.post<any>(url, request)
            .subscribe(response => {
                this.isLoading = 'loadend';
                if (job === 'regist') {
                    this.registFinish(response);
                } else if (job === 'login') {
                    this.loginFinish(response);
                }
            });
    }

    /**
     * CredentialCreatorOptionsをnavigator.createに渡し
     * ユーザー作成処理を行う
     * @param response 
     */
    private async registFinish(response: any): Promise<any>
    {
        const credential = await convertCredentialCreateOptions({
            publicKey: response.publicKeyCredentialCreationOptions
        });

        const _response = {
            registrationId: response.registrationId,
            credential
        };
        this.finishContact(
            _response,
            this.server + this.path + this.apis.registFinish
        );
    }

    /**
     * CredentialRequestOptionsをnavigator.getに渡し
     * 認証処理を行う
     * @param response 
     */
    private async loginFinish(response: any): Promise<any>
    {
        const credential = await convertCredentialRequestOptions({
            publicKey: response.publicKeyCredentialRequestOptions
        });

        const _response = {
            assertionId: response.assertionId,
            credential
        };
        this.finishContact(
            _response,
            this.server + this.path + this.apis.loginFinish
        );
    }

    /**
     * navigatorのレスポンスをサーバーに返す
     * @param response 
     * @param url 
     */
    private finishContact(response, url): any
    {
        this.isLoading = 'loading';
        this.httpClient.post<any>(
            url,
            response
        ).subscribe(response => {
            this.isLoading = 'loadend';
            if (response) {
                alert('Success');
            }
        });
    }

}
