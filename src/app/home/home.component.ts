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

    public _prop    = {
        message:    'aa',
    }
    public forms    = {
        userName:       '',
        clossPlatform:   true,
        builtIn:        false
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

    public checkForm(): boolean
    {
        if (this.forms.userName.length >= 2
        ) {
            return true;
        } else {
            return false;
        }
    }



    private login() 
    {
        const url = 'https://yasukosan.dip.jp/fido_angular/server/public/api/assertion/start';
        this.httpService.setServerURL(url);
        // 入力フォームの内容を取得
        const body = this.httpService.buildParams(
                this.credentialService.buildAuthCredential(
                    this.forms.userName, 'preferrd'
                )
            );
   
        this.httpClient.post<AuthCredentialOption>(url, body)
            .subscribe(response => {
                console.log(response);
                
                this.loginFinish(response);
            });
    }

    private loginFinish(response: any): any
    {

        this.credentialService.convertCredentialRequestOptions({
            publicKey: response.publicKeyCredentialRequestOptions
        })
        .then((credential)=>{
            console.log(credential);
            const url = 'https://yasukosan.dip.jp/fido_angular/server/public/api/assertion/finish';

            this.httpService.setServerURL(url);

            const assertionResponse = {
                assertionId: response.assertionId,
                credential
            };
            this.httpClient.post<AuthCredentialOption>(url, assertionResponse)
            .subscribe(response => {
                console.log(response);
                
                this.loginFinish(response);
            });
        });
    }

}
