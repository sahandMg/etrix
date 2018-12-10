import React , {Component} from 'react';
import axios from 'axios';
import {Link, withRouter} from 'react-router-dom';
import dataCode from '../../dataCode';
import { ClipLoader } from 'react-spinners';
import * as actions from '../../store/actions/index';
import Alert from 'react-s-alert';
import Modal from 'react-responsive-modal';
import { connect } from 'react-redux';
import './showSearchProductResult.css';
import URLs from "../../URLs";
import styles from './custom-styling.css';
import FilterProducts from './FilterProducts/FilterProducts';
import ProductTable from './ProductsTable/ProductsTable';

let prices = {};let counter = 0;

class showSearchProductResult extends Component {

    state  = {
        searchKey: '', data: '', dataParts: [], dataCode: '', dataFilters: [],open: false, prices: {}, projects: [],
        tableHeaderS: '', loading: true, number: 1,loadingAddCart: true,productName: '', category: '',
        projectName: null
    }

    componentDidMount() {prices = {};counter = 0;
        let url = URLs.base_URL+URLs.search_part_category+this.props.match.params.category+'&keyword='+this.props.match.params.keyword;
        if(this.props.match.params.filter != null) {
            url = url+"&filters="+this.props.match.params.filter
        }
        // let temp = window.location.href;
        // temp = temp.replace(URLs.react_search_url+this.props.match.params.category+'/'+this.props.match.params.keyword,'');
        // temp = temp.replace('/','');
        // console.log('componentDidMount temp');console.log(temp);
        console.log('componentDidMount url');console.log(url);
        // if(temp !== '') { url = url + '&filters='+temp; }
        this.setState({searchKey: this.props.match.params.keyword});
        // url = "http://localhost/api/search-part-filter?keyword=stm32f4&category=Embedded-Microcontrollers&filters=%7B%22rCl%22:[%2240MHz%22],%22tra%22:[%22Microchip+Technology%22]%7D";
        axios.get(url)
            .then(response => {
                console.log("componentDidMount showSearchProductResult");
                console.log(response);
                // console.log(dataCode.partSearch);
                if(response.data[0] === dataCode.partSearch) {
                    // console.log("IS EQUAL");
                    this.setState({dataCode: response.data[0],dataParts: response.data[2],dataFilters: response.data[3],tableHeaderS: response.data[5]});
                }
                this.setState({loading: false});
            })
            .catch(err => {
                console.log("componentDidMount searchKey");console.log(err);
            });
        if(this.props.token) { this.getProjects(); }
    }

    sort = (property,kind) => {
        console.log("sort");
        console.log(property);
        console.log(kind);
    }

    filterComponent = (filters) => {
        // console.log("filterComponent filters");
        // console.log(filters);
        // console.log("filterComponent JSON.stringify(filters)");
        // console.log(JSON.stringify(filters));
        let url = '/search/'+this.state.dataParts[0].slug+'/'+this.props.match.params.keyword+'/'+JSON.stringify(filters);
        url = url.replace('{',"%7B");
        url = url.replace('}',"%7D");
        // let url = buildUrl('/search/'+this.state.dataParts[0].slug+'/'+this.props.match.params.keyword+'/', {
        //     queryParams: {
        //         'filters': JSON.stringify(filters)
        //     }
        // });
        // console.log("filterComponent url");
        // console.log(url);
        // url = url.replace('?filters=','/');
        // console.log(url);
        this.props.history.push(url);
        window.location.reload();
    }

    // setNumber = (e,num) => {
    //     // console.log('num');console.log(num);console.log('e');console.log(e.target.value);
    //    let temp = this.state.number;temp[num] = e.target.value; this.setState({number: temp});
    // }

    addToCart = (productName,category,number) => {
       if(this.props.token) {
           this.setState({loadingAddCart: true});
           // console.log("number one :");console.log(this.state.number[productName]);
           axios.post(URLs.base_URL+URLs.user_cart_create, {
               keyword: productName,
               num: number,
               token: this.props.token, project: this.state.projectName
           })
               .then(response => {
                   console.log("add to cart");
                   console.log(response);console.log("this.state.projectName");console.log(this.state.projectName);
                   this.props.addToCart(productName, this.state.number[productName], category, this.state.projectName);
                   Alert.success('به سبد خرید اضافه شد', {
                       position: 'bottom-right',
                       effect: 'scale',
                       beep: false,
                       timeout: 4000,
                       offset: 100
                   });
                   this.setState({loadingAddCart: false});
               })
               .catch(err => {
                   console.log(err);
                   Alert.error('دوباره امتحن کنید', {
                       position: 'bottom-right',
                       effect: 'scale',
                       beep: false,
                       timeout: 4000,
                       offset: 100
                   });
                   this.setState({loadingAddCart: false});
               });

       } else {
           this.props.addToCart(productName, this.state.number, category, null);
           Alert.success('به سبد خرید اضافه شد', {
               position: 'bottom-right',
               effect: 'scale',
               beep: false,
               timeout: 4000,
               offset: 100
           });
       }
       this.onCloseModal();
    }

    setInitialForPriceInput = () => {
        let temp = this.state.number;
        if(Object.keys(temp).length == 0) {
            this.state.dataParts.map((item, i) => {
                 Object.keys(item).map((property, j) => {
                    if(property === "unit_price") {
                        temp[item['manufacturer_part_number']] = 1 ;
                        prices[item['manufacturer_part_number']] = 0;
                    }
                });
            });
            console.log("setInitialForPriceInput");console.log(temp);
            this.setState({number: temp});let pricesBuf = {};
            Object.keys(prices).map((property, j) => {
                console.log(j+" : "+ property);
                axios.post(URLs.base_URL+URLs.product_get_price, {keyword: property})
                    .then(response => {
                        console.log(j+" : ");counter++;
                        console.log(response);
                        pricesBuf[property] = response.data.unit_price;
                        if(counter === Object.keys(prices).length) {
                            console.log("get last response ");console.log(pricesBuf);
                            this.setState({prices: pricesBuf, loadingAddCart: false});
                        }
                    })
                    .catch(err => {
                        console.log(err);
                    });

            });

         }
    }

    onOpenModal = (productName,category,number) => {
        if(this.props.token) {
            this.setState({open: true});
            this.setState({productName: productName, category: category, number: number});
        } else {
            this.addToCart(productName,category,number);
        }
    };

    onCloseModal = () => {
        this.setState({ open: false });
    };

    getProjects = () => {
        axios.post(URLs.base_URL+URLs.user_get_projects, {token: this.props.token})
            .then(response => {
                console.log("projects");console.log(response);
                this.setState({projects: response.data});
            })
            .catch(err => {
                console.log("get projects error");
                console.log(err);
            });
    }

    selectChange = (event) => {
        this.setState({projectName: event.target.value});
    }

    render() {
        let productsTble;
        let projectsOption;
        if(this.state.dataCode === dataCode.partSearch) {
            productsTble = <ProductTable onOpenModal={this.onOpenModal} sort={this.sort} searchKey={this.state.searchKey} tableHeaderS={this.state.tableHeaderS} dataParts={this.state.dataParts} />;
            if(this.state.projects.length > 0) {
                projectsOption = this.state.projects.map((project, i) => {
                    return (<option value={project.name} key={project.name}>{project.name}</option>)
                });
            }
        }
        return(
            <div className="container table-responsive text-center searchResultContainer">
               <div>
                <ClipLoader loaderStyle={{size: '200'}} color={'#123abc'} loading={this.state.loading} />
               </div>
                <FilterProducts filterComponent={this.filterComponent} tableHeaderS={this.state.tableHeaderS} dataFilters={this.state.dataFilters} loading={this.state.loading} />
                {productsTble}
                <Modal open={this.state.open} onClose={this.onCloseModal} center
                       classNames={{overlay: styles.customOverlay, modal: styles.customModal,}}>
                  <div className="select-project">
                    <h3 className="text-center"> انتخاب پروژه</h3>
                    <br/>
                    <div className="col-lg-4 col-md-6 col-sm-10 horizontal-center">
                        <select className="form-control" value={this.state.projectName}  onChange={this.selectChange}>
                            <option value={null}>-</option>
                            {projectsOption}
                        </select>
                    </div>
                    <br/>
                    <button onClick={()=> this.addToCart(this.state.productName, this.state.category, this.state.number)} className="btn btn-success horizontal-center">اضافه به سبد خرید</button>
                    <br/>
                  </div>
                </Modal>
            </div>
        )
    }
}

const mapDispatchToProps = dispatch => {
    return {
        addToCart: (productName,number,category,projectName) => dispatch(actions.addToCart(productName,number,category,projectName)),
    };
};

const mapStateToProps = state => {
    return {
        token: state.auth.token,
    };
};

export default connect(mapStateToProps,mapDispatchToProps)(withRouter(showSearchProductResult));