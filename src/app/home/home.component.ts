import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

import {
    HttpService, CredentialService,
    AuthCredentialOption, publicKeyCredentialRequestOptions, 
} from '../_lib_service/index_helper';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: [
      './home.component.scss'
    ]
})

export class HomeComponent implements OnInit {

    public forms    = {
        userName:       '',
        clossPlatform:   true,
        builtIn:        false
    }

    private urls    = {
        'registFirst':      'https://yasukosan.dip.jp/fido_angular/server/public/api/registration/start',
        'registFinish':     'https://yasukosan.dip.jp/fido_angular/server/public/api/registration/finish',
        'loginFirst':       'https://yasukosan.dip.jp/fido_angular/server/public/api/assertion/start',
        'loginFinish':      'https://yasukosan.dip.jp/fido_angular/server/public/api/assertion/finish',
    }

    constructor(
        private readonly httpClient: HttpClient,
        private httpService: HttpService,
        private credentialService: CredentialService,
    ) {
    }

    ngOnInit(): void {
        // this.setup();
    }


    public doLogin(): void
    {
        this.login();
    }

    public doRegist(): void
    {
        this.regist();
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

    private regist()
    {
        this.firstContact(this.urls.registFirst, 'regist');
    }

    private login() 
    {
        this.firstContact(this.urls.loginFirst, 'login');
    }

    private firstContact(url: string, job: string): void
    {
        this.httpService.setServerURL(url);
        // 入力フォームの内容を取得
        const body = this.httpService.buildParams(
                this.credentialService.buildAuthCredential(
                    this.forms.userName, 'required'
                )
            );
   
        this.httpClient.post<AuthCredentialOption>(url, body)
            .subscribe(response => {
                console.log(response);
                if (job === 'regist') {
                    this.registFinish(response);
                } else if (job === 'login') {
                    this.loginFinish(response);
                }
            });
    }

    private registFinish(response: any): any
    {
        this.credentialService.convertCredentialCreateOptions({
            publicKey: response.publicKeyCredentialCreationOptions
        })
        .then((credential) => {
            console.log(credential);

            this.httpService.setServerURL(this.urls.registFinish);

            const assertionResponse = {
                registrationId: response.registrationId,
                credential
            };
            this.httpClient.post<AuthCredentialOption>(
                this.urls.registFinish,
                assertionResponse
            ).subscribe(response => {
                console.log(response);
                alert('Success');
            });
        });

    }

    private loginFinish(response: any): any
    {

        this.credentialService.convertCredentialRequestOptions({
            publicKey: response.publicKeyCredentialRequestOptions
        })
        .then((credential)=>{
            console.log(credential);

            this.httpService.setServerURL(this.urls.loginFinish);

            const assertionResponse = {
                assertionId: response.assertionId,
                credential
            };
            this.httpClient.post<AuthCredentialOption>(
                this.urls.loginFinish,
                assertionResponse
            ).subscribe(response => {
                console.log(response);
                if (response) {
                    alert('Success');
                }
            });
        });
    }

}
