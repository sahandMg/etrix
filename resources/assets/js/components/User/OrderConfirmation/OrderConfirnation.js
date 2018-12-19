import React, { Component } from 'react';
import { connect } from 'react-redux';
import CardWrapper from "../../CardWrapper/CardWrapper";
import URLs from "../../../URLs";
import axios from 'axios';
import OrderProject from './OrderProject/OrderProject';

class OrderConfirnation extends Component {
    state = {
        address: '', phone: '', codePost: '', city: '', province: '',
        postPrice: 0,  projects: [],
        errors: {}, factorNumber: '', priceAllProjects: '',
    }

    componentDidMount() {
        console.log("OrderConfirnation componentDidMount");
        axios.post(URLs.base_URL+URLs.user_cart_confirm, {token: this.props.token})
            .then(response => {
                console.log("OrderConfirnation response");console.log(response);
                if(response.data.cart.length>0) {
                    this.setState({
                        address: response.data.address,
                        projects: response.data.cart,
                        city: response.data.city,
                        factorNumber: response.data.number,
                        province: response.data.province,
                        priceAllProjects: response.data.price,
                        postPrice: response.data.delivery
                    });
                }
            })
            .catch(err => {
                console.log("OrderConfirnation error");
                console.log(err);
            });
    }
    onChange = e =>
        this.setState({
            data: { ...this.state.data, [e.target.name]: e.target.value }
        });

    confirmFactor = () => {

    }


    render() {
        console.log("OrderConfirnation render");
        let orderList = this.state.projects.map( (project, i) => {
            return (
                <OrderProject  key={i} project={project} />
            );
        });

        return (
            <div className="container" style={{direction: 'rtl'}}>
                <CardWrapper>
                    <h1 className="text-center">پیش فاکتور</h1>
                    <hr/>
                    <br/>
                    <h3 className="text-right"> شماره فاکتور: {this.state.factorNumber}</h3>
                    <br/>
                    {orderList}
                    <h3 className="text-center">هزینه کل قطعات : {this.state.priceAllProjects} تومان</h3>
                    <h3 className="text-center">هزینه ارسال سفارش : {this.state.postPrice} تومان</h3>
                    <br/>
                    <h3 className="text-center">مبلغ پرداختی سفارش : {parseInt(this.state.postPrice) + parseInt(this.state.priceAllProjects)} تومان</h3>
                    <br/>
                    <button type="submit" className="btn btn-success" style={{display: 'block', margin: 'auto'}}>پرداخت</button>
                </CardWrapper>
                <br/><br/><br/><br/>
            </div>
        )
    }
}

const mapStateToProps = state => {
    return {
        token: state.auth.token,
    };
};

export default connect(mapStateToProps,null)(OrderConfirnation);

