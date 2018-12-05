import React , {Component} from 'react';
import { ClipLoader } from 'react-spinners';
import axios from 'axios';
import URLs from '../../../../URLs';

class OrdersList extends Component {
    state = {
        orders: [], loading: true
    }

    componentDidMount() {
        // axios.post(URLs.cm_get_ordersList, {ordersStatus: this.props.status})
        //     .then((response) => {
        //        this.setState({orders: response.data});
        //     })
        //     .catch(err => {
        //         console.log("err");
        //         console.log(err);
        //     });
    }

    nextStaet = () => {
        if(this.props.status === "checking") {
            return <button className="btn">تایید سفارش</button>
        }
        if(this.props.status === "confirmed") {
            return <button className="btn">جمع آوری سفارش</button>
        }
        if(this.props.status === "collecting") {
            return <button className="btn">ارسال شده</button>
        }
        if(this.props.status === "sent") {
            return <button className="btn">تحویل داده شده</button>
        }
    }

    render() {
        let table = <table className="table table-striped">
            <thead>
            <tr>
                <th>شماره فاکتور</th>
                <th>تاریخ ثبت</th>
                <th>وضعیت</th>
                <th>تغییر به وضعیت</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>1234</td>
                <td>1997/06/22, 10:45</td>
                <td>{this.props.listName}</td>
                <td>{this.nextStaet()}</td>
            </tr>
            <tr>
                <td>1234</td>
                <td>1997/06/22, 10:45</td>
                <td>{this.props.listName}</td>
                <td>{this.nextStaet()}</td>
            </tr>
            <tr>
                <td>1234</td>
                <td>1997/06/22, 10:45</td>
                <td>{this.props.listName}</td>
                <td>{this.nextStaet()}</td>
            </tr>
            </tbody>
        </table>;
        return (
            <div>
                <h3 className="text-center">{this.props.listName}</h3>
                {table}
                <ul className="pagination">
                    <li className="page-item disabled"><button className="page-link">Previous</button></li>
                    <li className="page-item"><button className="page-link">1</button></li>
                    <li className="page-item"><button className="page-link">2</button></li>
                    <li className="page-item"><button className="page-link">3</button></li>
                    <li className="page-item"><button className="page-link">Next</button></li>
                </ul>
            </div>
        )
    }
};

export default OrdersList;